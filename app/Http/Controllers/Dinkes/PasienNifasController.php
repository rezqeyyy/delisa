<?php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Excel .xlsx
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use Illuminate\Pagination\LengthAwarePaginator;

// Eloquent Models
use App\Models\Pasien;
use App\Models\PasienNifasBidan;
use App\Models\PasienNifasRs;
use App\Models\AnakPasien;
use App\Models\KfKunjungan;        // ✅ PAKAI TABEL kf_kunjungans
use App\Models\Puskesmas;

class PasienNifasController extends Controller
{
    /**
     * Halaman index pemantauan pasien nifas Dinkes.
     */
    public function index(Request $request)
    {
        $q           = trim($request->get('q', ''));
        $puskesmasId = $request->get('puskesmas_id');
        $sort        = $request->get('sort', 'prioritas');
        $priority    = $request->get('priority'); // 'hitam','merah','kuning','hijau','tanpa_jadwal' atau null

        // Query dasar: pasien nifas + pasien + user + puskesmas (dari skrining terbaru)
        $baseQuery = $this->buildPasienNifasQuery(
            $q,
            $puskesmasId ? (int) $puskesmasId : null
        );

        // Ambil semua episode nifas (diproses di Collection lalu dipaginate manual)
        $allRows = $baseQuery->get();

        // Kumpulkan id_nifas untuk cek progres KF
        $nifasIds = $allRows->pluck('nifas_id')->filter()->values()->all();

        $kfDone = collect();
        if (!empty($nifasIds)) {
            // Sekarang pakai kf_kunjungans: pasien_nifas_id + jenis_kf
            $kfDone = KfKunjungan::query()
                ->selectRaw('pasien_nifas_id, MAX(jenis_kf)::int as max_ke')
                ->whereIn('pasien_nifas_id', $nifasIds)
                ->groupBy('pasien_nifas_id')
                ->get()
                ->keyBy('pasien_nifas_id');
        }

        // Konfigurasi jadwal KF (hari setelah tanggal_mulai_nifas)
        $dueDays = [
            1 => 3,
            2 => 7,
            3 => 14,
            4 => 42,
        ];

        $today  = Carbon::today();
        $namaKf = [
            1 => 'satu',
            2 => 'dua',
            3 => 'tiga',
            4 => 'empat',
        ];

        // Hitung jadwal KF dan sisa waktu + prioritas (PASTI TERAPLIKASI)
        $allRows = $allRows->map(function ($row) use ($kfDone, $dueDays, $today, $namaKf) {
            return $this->hitungKfDanPrioritas($row, $kfDone, $dueDays, $today, $namaKf);
        });

        // Filter berdasarkan warna prioritas (opsional)
        if (!empty($priority)) {
            $priorityMap = [
                'hitam'        => 1, // terlambat
                'merah'        => 2, // sisa 0–3 hari
                'kuning'       => 3, // sisa 4–6 hari
                'hijau'        => 4, // sisa ≥ 7 hari
                'tanpa_jadwal' => 5, // jadwal KF belum tersedia / tidak ada tanggal nifas
            ];

            if (isset($priorityMap[$priority])) {
                $targetLevel = $priorityMap[$priority];

                $allRows = $allRows->filter(function ($row) use ($targetLevel) {
                    return ($row->priority_level ?? null) === $targetLevel;
                });
            }
        }

        // Sorting sesuai pilihan
        switch ($sort) {
            case 'nama_asc':
                $allRows = $allRows->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
                break;
            case 'nama_desc':
                $allRows = $allRows->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
                break;
            case 'kf_terbaru':
                $allRows = $allRows->sortByDesc(function ($row) {
                    return $row->jadwal_kf_date ?: Carbon::create(1900, 1, 1);
                });
                break;
            case 'kf_terlama':
                $allRows = $allRows->sortBy(function ($row) {
                    return $row->jadwal_kf_date ?: Carbon::create(2100, 1, 1);
                });
                break;
            default:
                // Default: prioritas warna + yang paling mepet di dalam tiap level
                $allRows = $allRows
                    ->sortBy(function ($row) {
                        return $row->hari_sisa ?? 9999;
                    })
                    ->sortBy('priority_level');
                break;
        }

        $allRows = $allRows->values();

        // Paginate manual
        $perPage = 10;
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $total   = $allRows->count();

        $currentItems = $allRows->forPage($page, $perPage)->values();

        $rows = new LengthAwarePaginator(
            $currentItems,
            $total,
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Daftar puskesmas (hanya induk, is_mandiri = 0) untuk filter
        $puskesmasList = Puskesmas::query()
            ->where('is_mandiri', 0)
            ->orderBy('nama_puskesmas')
            ->get();

        return view('dinkes.pasien-nifas.pasien-nifas', [
            'rows'                => $rows,
            'q'                   => $q,
            'puskesmasList'       => $puskesmasList,
            'selectedPuskesmasId' => $puskesmasId,
            'sort'                => $sort,
            'priority'            => $priority, // dipakai di Blade untuk label sort
        ]);
    }

    /**
     * Query dasar pasien nifas.
     */
    private function buildPasienNifasQuery(?string $q = '', ?int $puskesmasId = null)
    {
        $q = trim($q ?? '');

        // Subquery skrining terbaru per pasien
        $latestSkriningSql = <<<SQL
            (
                SELECT DISTINCT ON (pasien_id)
                       id,
                       pasien_id,
                       puskesmas_id,
                       created_at
                FROM skrinings
                ORDER BY pasien_id, created_at DESC
            ) AS ls
        SQL;

        $query = Pasien::query()
            ->from('pasiens as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('pasien_nifas_bidan as pnb', 'pnb.pasien_id', '=', 'p.id')
            ->leftJoin('pasien_nifas_rs as pnr', 'pnr.pasien_id', '=', 'p.id')
            // skrining terbaru (mengandung puskesmas induk → ini yang jadi kolom "Puskesmas")
            ->leftJoin(DB::raw($latestSkriningSql), 'ls.pasien_id', '=', 'p.id')
            // puskesmas asal skrining
            ->leftJoin('puskesmas as pk', 'pk.id', '=', 'ls.puskesmas_id')
            ->selectRaw(<<<'SQL'
                p.id as pasien_id,
                u.name,
                p.nik,
                p.tempat_lahir,
                p.tanggal_lahir,
                CASE 
                    WHEN pnb.id IS NOT NULL THEN 'Bidan'
                    WHEN pnr.id IS NOT NULL THEN 'Rumah Sakit'
                    ELSE 'Puskesmas'
                END as role_penanggung,
                COALESCE(pnb.id, pnr.id) as nifas_id,
                CASE
                    WHEN pnb.id IS NOT NULL THEN 'bidan'
                    WHEN pnr.id IS NOT NULL THEN 'rs'
                    ELSE NULL
                END as sumber_nifas,
                COALESCE(pnb.tanggal_mulai_nifas, pnr.tanggal_mulai_nifas) as tanggal_mulai_nifas,
                pk.id as puskesmas_id,
                pk.nama_puskesmas as puskesmas_nama
            SQL);

        // Hanya pasien yang punya episode nifas
        $query->where(function ($w) {
            $w->whereNotNull('pnb.id')
                ->orWhereNotNull('pnr.id');
        });

        // Pencarian nama / NIK
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('u.name', 'ILIKE', "%{$q}%")
                    ->orWhere('p.nik', 'ILIKE', "%{$q}%");
            });
        }

        // Filter berdasarkan puskesmas skrining
        if (!is_null($puskesmasId)) {
            $query->where('pk.id', $puskesmasId);
        }

        // Order dasar
        return $query->orderBy('u.name');
    }

    /**
     * Export Excel (menghormati filter q + puskesmas_id + sort).
     */
    public function export(Request $request)
    {
        $q           = trim($request->get('q', ''));
        $puskesmasId = $request->get('puskesmas_id');
        $sort        = $request->get('sort', 'prioritas');
        $priority    = $request->get('priority');

        // 1) Ambil data dasar pasien nifas (sama seperti index)
        $baseQuery = $this->buildPasienNifasQuery(
            $q,
            $puskesmasId ? (int) $puskesmasId : null
        );

        $rows = $baseQuery->get();

        // 2) Hitung progres KF + priority level, supaya urutan export
        //    mengikuti sort yang sama dengan halaman index.
        $nifasIds = $rows->pluck('nifas_id')->filter()->values()->all();

        $kfDone = collect();
        if (!empty($nifasIds)) {
            $kfDone = KfKunjungan::query()
                ->selectRaw('pasien_nifas_id, MAX(jenis_kf)::int as max_ke')
                ->whereIn('pasien_nifas_id', $nifasIds)
                ->groupBy('pasien_nifas_id')
                ->get()
                ->keyBy('pasien_nifas_id');
        }

        $dueDays = [
            1 => 3,
            2 => 7,
            3 => 14,
            4 => 42,
        ];

        $today  = Carbon::today();
        $namaKf = [
            1 => 'satu',
            2 => 'dua',
            3 => 'tiga',
            4 => 'empat',
        ];

        // Terapkan logika KF yang sama persis dengan index()
        $rows = $rows->map(function ($row) use ($kfDone, $dueDays, $today, $namaKf) {
            return $this->hitungKfDanPrioritas($row, $kfDone, $dueDays, $today, $namaKf);
        });

        // Filter berdasarkan warna prioritas (opsional)
        if (!empty($priority)) {
            $priorityMap = [
                'hitam'       => 1,
                'merah'       => 2,
                'kuning'      => 3,
                'hijau'       => 4,
                'tanpa_jadwal' => 5,
            ];

            if (isset($priorityMap[$priority])) {
                $targetLevel = $priorityMap[$priority];

                $rows = $rows->filter(function ($row) use ($targetLevel) {
                    return ($row->priority_level ?? null) === $targetLevel;
                });
            }
        }

        // Sorting sama seperti index()
        switch ($sort) {
            case 'nama_asc':
                $rows = $rows->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
                break;
            case 'nama_desc':
                $rows = $rows->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
                break;
            case 'kf_terbaru':
                $rows = $rows->sortByDesc(function ($row) {
                    return $row->jadwal_kf_date ?: Carbon::create(1900, 1, 1);
                });
                break;
            case 'kf_terlama':
                $rows = $rows->sortBy(function ($row) {
                    return $row->jadwal_kf_date ?: Carbon::create(2100, 1, 1);
                });
                break;
            default:
                $rows = $rows
                    ->sortBy(function ($row) {
                        return $row->hari_sisa ?? 9999;
                    })
                    ->sortBy('priority_level');
                break;
        }

        $rows = $rows->values();

        // === MULAI BAGIAN STYLING EXCEL (TIDAK DIUBAH) ===
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Judul
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'Laporan Data Pasien Nifas');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(5);

        // Header
        $headerRow = 3;
        $headers   = [
            'A' => 'No',
            'B' => 'Nama Lengkap',
            'C' => 'NIK',
            'D' => 'Puskesmas',
            'E' => 'Jadwal KF',
            'F' => 'Sisa Waktu',
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . $headerRow, $text);
        }

        $headerRange = 'A' . $headerRow . ':F' . $headerRow;

        $sheet->getStyle($headerRange)->getFont()
            ->setBold(true)
            ->getColor()->setARGB('FFFFFFFF');

        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4F81BD');

        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(15);

        $sheet->getStyle('C')->getNumberFormat()->setFormatCode('@');

        // Data
        $rowIndex = $headerRow + 1;
        $no       = 1;

        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $rowIndex, $no);
            $sheet->setCellValue('B' . $rowIndex, $row->name);

            $sheet->setCellValueExplicit(
                'C' . $rowIndex,
                $row->nik,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );

            $sheet->setCellValue('D' . $rowIndex, $row->puskesmas_nama ?? '-');
            $sheet->setCellValue('E' . $rowIndex, $row->jadwal_kf_text ?? '');
            $sheet->setCellValue('F' . $rowIndex, $row->sisa_waktu_label ?? '');

            $rowIndex++;
            $no++;
        }

        $lastDataRow = $rowIndex - 1;
        if ($lastDataRow < $headerRow) {
            $lastDataRow = $headerRow;
        }

        $tableRange = 'A' . $headerRow . ':F' . $lastDataRow;

        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        for ($r = $headerRow; $r <= $lastDataRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        $sheet->freezePane('A' . ($headerRow + 1));

        $fileName = 'data-pasien-nifas-' . now()->format('Y-m-d') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Helper: Hitung jadwal KF berikutnya, sisa waktu, teks, dan prioritas.
     * - Belum pernah KF → jadwal KF1 tetap dihitung.
     * - Hari ini (0 hari) → dianggap masih sisa waktu (badge merah, bukan telat).
     * - Telat → badge hitam, teks "Sisa -X Hari".
     */
    private function hitungKfDanPrioritas($row, $kfDone, array $dueDays, Carbon $today, array $namaKf)
    {
        $nifasId = $row->nifas_id;

        $hasKf = $nifasId && $kfDone->has($nifasId);

        if ($hasKf) {
            $maxKe  = optional($kfDone->get($nifasId))->max_ke ?? 0;
            $nextKe = min(4, $maxKe + 1); // maksimal KF4
        } else {
            // Belum pernah KF sama sekali → mulai dari KF1
            $maxKe  = 0;
            $nextKe = 1;
        }

        // SIMPAN KF MAKSIMAL YANG SUDAH DILAKUKAN → dipakai di view
        $row->max_kf_done = (int) $maxKe;

        $row->next_kf_ke = $nextKe;

        $tanggalMulai = $row->tanggal_mulai_nifas
            ? Carbon::parse($row->tanggal_mulai_nifas)
            : null;

        $jadwalDate = null;
        $hariSisa   = null;

        if ($tanggalMulai) {
            $due        = $dueDays[$nextKe] ?? 42;
            $jadwalDate = $tanggalMulai->copy()->addDays($due);
            // >0: masih X hari lagi, 0: hari ini, <0: sudah lewat X hari
            $hariSisa   = $today->diffInDays($jadwalDate, false);
        }

        $row->jadwal_kf_date = $jadwalDate;
        $row->hari_sisa      = $hariSisa;

        // === Teks kolom "Jadwal KF" ===
        if ($jadwalDate) {
            // Pakai format KF1, KF2, KF3, KF4 (bukan "KF satu/dua/tiga/empat")
            $kfLabel = 'KF' . $nextKe;

            if ($hariSisa === null) {
                $row->jadwal_kf_text = "Jadwal {$kfLabel} belum diketahui";
            } elseif ($hariSisa > 0) {
                $row->jadwal_kf_text = sprintf(
                    '%s akan dilakukan pada tanggal %s',
                    $kfLabel,
                    // Paksa locale Indonesia → Januari, Februari, dst.
                    $jadwalDate->locale('id')->translatedFormat('d F Y')
                );
            } elseif ($hariSisa === 0) {
                $row->jadwal_kf_text = sprintf(
                    'Hari ini jadwal %s',
                    $kfLabel
                );
            } else {
                $row->jadwal_kf_text = sprintf(
                    '%s sudah terlewat %d hari',
                    $kfLabel,
                    abs($hariSisa)
                );
            }
        } else {
            $row->jadwal_kf_text = 'Jadwal KF belum tersedia';
        }


        // === Badge "Sisa Waktu" (UI seperti di Figma) ===
        if ($hariSisa === null) {
            // Tidak ada tanggal nifas → tidak bisa dihitung
            $row->sisa_waktu_label = '—';
            $row->badge_class      = 'bg-[#E5E7EB] text-[#374151]';
            $row->priority_level   = 5;
        } else {
            if ($hariSisa >= 7) {
                // 1) Sisa ≥ 7 hari → Hijau
                $row->sisa_waktu_label = "Sisa {$hariSisa} Hari";
                $row->badge_class      = 'bg-[#2EDB58] text-white';
                $row->priority_level   = 4;
            } elseif ($hariSisa >= 4) {
                // 2) 4–6 hari → Kuning
                $row->sisa_waktu_label = "Sisa {$hariSisa} Hari";
                $row->badge_class      = 'bg-[#FFC400] text-[#1D1D1D]';
                $row->priority_level   = 3;
            } elseif ($hariSisa >= 0) {
                // 3) 0–3 hari → Merah
                $row->sisa_waktu_label = "Sisa {$hariSisa} Hari";
                $row->badge_class      = 'bg-[#FF3B30] text-white';
                $row->priority_level   = 2;
            } else {
                // 4) Telat (hariSisa < 0) → Hitam, minus di label
                $telat = abs($hariSisa);
                $row->sisa_waktu_label = "Sisa -{$telat} Hari";
                $row->badge_class      = 'bg-[#000000] text-white';
                $row->priority_level   = 1;
            }
        }

        return $row;
    }

    /**
     * Lepas status nifas pasien (bidan & RS) tanpa menghapus data pasien.
     */
    public function destroy($pasienId)
    {
        DB::transaction(function () use ($pasienId) {
            PasienNifasBidan::where('pasien_id', $pasienId)->delete();
            PasienNifasRs::where('pasien_id', $pasienId)->delete();
        });

        return back()->with('success', 'Pasien dihapus dari daftar nifas.');
    }
}
