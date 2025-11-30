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
use App\Models\Kf;
use App\Models\Puskesmas;

class PasienNifasController extends Controller
{
    /**
     * Halaman index pemantauan pasien nifas Dinkes.
     * - Pencarian nama/NIK (q)
     * - Filter puskesmas (puskesmas_id)
     * - Sort:
     *      prioritas (hitam→merah→kuning→hijau) [default]
     *      nama_asc / nama_desc
     *      kf_terbaru / kf_terlama
     * - Hitung progres KF & sisa waktu (jika data KF sudah ada).
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
            $kfDone = Kf::query()
                ->selectRaw('id_nifas, MAX(kunjungan_nifas_ke)::int as max_ke')
                ->whereIn('id_nifas', $nifasIds)
                ->groupBy('id_nifas')
                ->get()
                ->keyBy('id_nifas');
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

        // Hitung jadwal KF dan sisa waktu
        $allRows = $allRows->map(function ($row) use ($kfDone, $dueDays, $today, $namaKf) {
            // Jika BELUM ada data KF sama sekali untuk nifas ini
            if (!$kfDone->has($row->nifas_id)) {
                $row->next_kf_ke      = null;
                $row->jadwal_kf_date  = null;
                $row->hari_sisa       = null;
                $row->jadwal_kf_text  = '';                    // jadwal KF dikosongkan
                $row->sisa_waktu_label = '—';                  // badge netral
                $row->badge_class      = 'bg-[#E5E7EB] text-[#374151]';
                $row->priority_level   = 5;                    // prioritas paling rendah
                return $row;
            }

            

            $maxKe = optional($kfDone->get($row->nifas_id))->max_ke ?? 0;
            $nextKe = min(4, $maxKe + 1); // Maksimal KF4
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

            // Teks keterangan jadwal KF
            if ($jadwalDate) {
                $kfLabel = $namaKf[$nextKe] ?? (string) $nextKe;

                if ($hariSisa !== null && $hariSisa > 0) {
                    $row->jadwal_kf_text = sprintf(
                        'KF %s akan dilakukan tanggal %s',
                        $kfLabel,
                        $jadwalDate->translatedFormat('d F Y')
                    );
                } elseif ($hariSisa !== null && $hariSisa <= 0) {
                    $telat = abs($hariSisa);
                    $row->jadwal_kf_text = sprintf(
                        'KF %s sudah terlewat %d hari',
                        $kfLabel,
                        $telat
                    );
                } else {
                    $row->jadwal_kf_text = sprintf(
                        'KF %s (jadwal tidak diketahui)',
                        $kfLabel
                    );
                }
            } else {
                $row->jadwal_kf_text = 'Jadwal KF belum tersedia';
            }

            // Badge sisa waktu + prioritas
            if ($hariSisa === null) {
                $row->sisa_waktu_label = '—';
                $row->badge_class      = 'bg-[#E5E7EB] text-[#374151]';
                $row->priority_level   = 5;
            } else {
                if ($hariSisa >= 7) {
                    // Hijau
                    $row->sisa_waktu_label = "Sisa {$hariSisa} hari";
                    $row->badge_class      = 'bg-[#2EDB58] text-white';
                    $row->priority_level   = 4;
                } elseif ($hariSisa >= 4) {
                    // Kuning
                    $row->sisa_waktu_label = "Sisa {$hariSisa} hari";
                    $row->badge_class      = 'bg-[#FFC400] text-[#1D1D1D]';
                    $row->priority_level   = 3;
                } elseif ($hariSisa >= 1) {
                    // Merah
                    $row->sisa_waktu_label = "Sisa {$hariSisa} hari";
                    $row->badge_class      = 'bg-[#FF3B30] text-white';
                    $row->priority_level   = 2;
                } else {
                    // Hitam (telat)
                    $telat = abs($hariSisa);
                    $row->sisa_waktu_label = "Terlambat {$telat} hari";
                    $row->badge_class      = 'bg-[#000000] text-white';
                    $row->priority_level   = 1;
                }
            }

            return $row;
        });

         // Filter berdasarkan warna prioritas (opsional)
        if (!empty($priority)) {
            $priorityMap = [
                'hitam'       => 1, // terlambat
                'merah'       => 2, // sisa 1–3 hari
                'kuning'      => 3, // sisa 4–6 hari (sesuai configmu)
                'hijau'       => 4, // sisa ≥ 7 hari
                'tanpa_jadwal'=> 5, // jadwal KF belum tersedia / tidak ada KF
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
        ]);
    }

    /**
     * Query dasar pasien nifas:
     * - pasien + users
     * - episode nifas bidan / RS
     * - puskesmas dari skrining terbaru pasien
     */
    private function buildPasienNifasQuery(?string $q = '', ?int $puskesmasId = null)
    {
        $q = trim($q ?? '');

        // Subquery skrining terbaru per pasien (gunakan delisa.sql → PostgreSQL)
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
            // skrining terbaru
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
     * Export Excel (menghormati filter q + puskesmas_id + sort)
     * TANPA mengubah styling tabel Excel.
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
            $kfDone = Kf::query()
                ->selectRaw('id_nifas, MAX(kunjungan_nifas_ke)::int as max_ke')
                ->whereIn('id_nifas', $nifasIds)
                ->groupBy('id_nifas')
                ->get()
                ->keyBy('id_nifas');
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

        $rows = $rows->map(function ($row) use ($kfDone, $dueDays, $today, $namaKf) {
            // sama persis logika di index()

            if (!$kfDone->has($row->nifas_id)) {
                $row->next_kf_ke       = null;
                $row->jadwal_kf_date   = null;
                $row->hari_sisa        = null;
                $row->jadwal_kf_text   = '';
                $row->sisa_waktu_label = '—';
                $row->badge_class      = 'bg-[#E5E7EB] text-[#374151]';
                $row->priority_level   = 5;
                return $row;
            }

            $maxKe  = optional($kfDone->get($row->nifas_id))->max_ke ?? 0;
            $nextKe = min(4, $maxKe + 1);
            $row->next_kf_ke = $nextKe;

            $tanggalMulai = $row->tanggal_mulai_nifas
                ? Carbon::parse($row->tanggal_mulai_nifas)
                : null;

            $jadwalDate = null;
            $hariSisa   = null;

            if ($tanggalMulai) {
                $due        = $dueDays[$nextKe] ?? 42;
                $jadwalDate = $tanggalMulai->copy()->addDays($due);
                $hariSisa   = $today->diffInDays($jadwalDate, false);
            }

            $row->jadwal_kf_date = $jadwalDate;
            $row->hari_sisa      = $hariSisa;

            if ($hariSisa === null) {
                $row->sisa_waktu_label = '—';
                $row->badge_class      = 'bg-[#E5E7EB] text-[#374151]';
                $row->priority_level   = 5;
            } else {
                if ($hariSisa >= 7) {
                    $row->sisa_waktu_label = "Sisa {$hariSisa} hari";
                    $row->badge_class      = 'bg-[#2EDB58] text-white';
                    $row->priority_level   = 4;
                } elseif ($hariSisa >= 4) {
                    $row->sisa_waktu_label = "Sisa {$hariSisa} hari";
                    $row->badge_class      = 'bg-[#FFC400] text-[#1D1D1D]';
                    $row->priority_level   = 3;
                } elseif ($hariSisa >= 1) {
                    $row->sisa_waktu_label = "Sisa {$hariSisa} hari";
                    $row->badge_class      = 'bg-[#FF3B30] text-white';
                    $row->priority_level   = 2;
                } else {
                    $telat                 = abs($hariSisa);
                    $row->sisa_waktu_label = "Terlambat {$telat} hari";
                    $row->badge_class      = 'bg-[#000000] text-white';
                    $row->priority_level   = 1;
                }
            }

            return $row;
        });

        // Filter berdasarkan warna prioritas (opsional)
        if (!empty($priority)) {
            $priorityMap = [
                'hitam'       => 1,
                'merah'       => 2,
                'kuning'      => 3,
                'hijau'       => 4,
                'tanpa_jadwal'=> 5,
            ];

            if (isset($priorityMap[$priority])) {
                $targetLevel = $priorityMap[$priority];

                $rows = $rows->filter(function ($row) use ($targetLevel) {
                    return ($row->priority_level ?? null) === $targetLevel;
                });
            }
        }

        // 3) Sorting sama seperti index()
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

        // 4) === MULAI BAGIAN STYLING EXCEL (TIDAK DIUBAH) ===
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

        // Header (kolom disesuaikan dengan tabel pemantauan)
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
            // Kolom A: No urut (seperti di tabel)
            $sheet->setCellValue('A' . $rowIndex, $no);

            // Kolom B: Nama
            $sheet->setCellValue('B' . $rowIndex, $row->name);

            // Kolom C: NIK (tetap pakai explicit string supaya tidak dipotong)
            $sheet->setCellValueExplicit(
                'C' . $rowIndex,
                $row->nik,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );

            // Kolom D: Puskesmas
            $sheet->setCellValue('D' . $rowIndex, $row->puskesmas_nama ?? '-');

            // Kolom E: Jadwal KF (teks lengkap seperti di tabel)
            // contoh: "KF satu sudah terlewat 2 hari" atau kosong jika belum ada KF
            $sheet->setCellValue('E' . $rowIndex, $row->jadwal_kf_text ?? '');

            // Kolom F: Sisa Waktu (label badge, misal: "—", "Sisa 3 hari", "Terlambat 1 hari")
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
