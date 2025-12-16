<?php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

class PasienNifasController extends Controller
{
    /**
     * Halaman index pemantauan pasien nifas Dinkes.
     */
    public function index(Request $request)
    {
        $q        = trim($request->get('q', ''));
        $faskesId = $request->get('faskes_id'); // contoh: "puskesmas:12" / "bidan:3" / "rs:5"
        $sort     = $request->get('sort', 'prioritas');
        $priority = $request->get('priority'); // 'hitam','merah','kuning','hijau','tanpa_jadwal' atau null

        // Query dasar: pasien nifas + pasien + user + puskesmas (dari skrining terbaru)
        $baseQuery = $this->buildPasienNifasQuery($q, $faskesId);

        // Ambil semua episode nifas (diproses di Collection lalu dipaginate manual)
        $allRows = $baseQuery->get();

        /**
         * ==========================================
         *  🔍 AMBIL PROGRES KF BERDASARKAN
         *  PASIEN_ID + EPISODE NIFAS RS TERBARU
         * ==========================================
         */
        $kfDone    = collect();
        $pasienIds = $allRows->pluck('pasien_id')->filter()->values()->all();

        $rsEpisodeIds = [];
        if (!empty($pasienIds)) {
            // Ambil episode nifas RS terbaru per pasien
            $rsEpisodes = DB::table('pasien_nifas_rs')
                ->select('id', 'pasien_id', 'tanggal_mulai_nifas', 'tanggal_melahirkan', 'created_at')
                ->whereIn('pasien_id', $pasienIds)
                ->orderByDesc('created_at')
                ->get();

            // Ambil hanya episode RS TERBARU per pasien
            $rsByPasien = [];
            foreach ($rsEpisodes as $ep) {
                if (!isset($rsByPasien[$ep->pasien_id])) {
                    $rsByPasien[$ep->pasien_id] = $ep;
                }
            }

            foreach ($rsByPasien as $pid => $ep) {
                if (!empty($ep->id)) {
                    $rsEpisodeIds[] = $ep->id;
                }
            }

            if (!empty($rsEpisodeIds)) {
                // Hitung max KF per episode RS
                $kfByEpisode = DB::table('kf_kunjungans')
                    ->selectRaw('pasien_nifas_id, MAX(jenis_kf)::int as max_ke')
                    ->whereIn('pasien_nifas_id', $rsEpisodeIds)
                    ->groupBy('pasien_nifas_id')
                    ->get()
                    ->keyBy('pasien_nifas_id');

                // Deteksi episode nifas yang punya kesimpulan Meninggal/Wafat
                $deathEpisodeIds = DB::table('kf_kunjungans as kk')
                    ->whereIn('kk.pasien_nifas_id', $rsEpisodeIds)
                    ->whereRaw("LOWER(COALESCE(kk.kesimpulan_pantauan,'')) IN ('meninggal','wafat')")
                    ->distinct()
                    ->pluck('kk.pasien_nifas_id')
                    ->toArray();

                // Remap: pasien_id => info KF episode RS terbarunya
                $map = [];
                foreach ($rsByPasien as $pid => $ep) {
                    $episodeStat = $kfByEpisode->get($ep->id);
                    if ($episodeStat) {
                        $map[$pid] = (object) [
                            'max_ke'       => $episodeStat->max_ke,
                            'is_meninggal' => in_array($ep->id, $deathEpisodeIds, true),
                        ];
                    }
                }

                $kfDone = collect($map);
            }

            Log::debug('Dinkes Pasien Nifas - Mapping KF per pasien', [
                'total_rows'        => $allRows->count(),
                'pasien_ids'        => $pasienIds,
                'rs_episode_ids'    => $rsEpisodeIds,
                'kf_done_perPasien' => $kfDone->toArray(),
            ]);
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

        // Hitung jadwal KF dan sisa waktu + prioritas
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
                'tanpa_jadwal' => 5, // jadwal KF belum tersedia / tidak ada tanggal nifas / sudah selesai semua KF
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

        /**
         * ==========================================================
         * ✅ FIX UTAMA ERROR faskes_key:
         * Jangan distinct langsung dari query utama,
         * tapi bungkus dulu jadi subquery agar alias jadi kolom nyata.
         * ==========================================================
         */
        $sub = $this->buildPasienNifasQuery('', null)->toBase();
        $faskesList = DB::query()
            ->fromSub($sub, 'q')
            ->selectRaw("
                faskes_key,
                CASE
                    WHEN faskes_tipe = 'puskesmas' THEN ('Puskesmas ' || faskes_nama)
                    WHEN faskes_tipe = 'bidan' THEN faskes_nama
                    ELSE faskes_nama
                END as faskes_nama
    ")
            ->distinct()
            ->orderBy('faskes_nama')
            ->get();

        return view('dinkes.pasien-nifas.pasien-nifas', [
            'rows'              => $rows,
            'q'                 => $q,
            'faskesList'        => $faskesList,
            // ✅ alias untuk Blade lama yang masih pakai $puskesmasList
            'puskesmasList'     => $faskesList,
            'selectedFaskesId'  => $faskesId,
            'sort'              => $sort,
            'priority'          => $priority,
        ]);
    }


    /**
     * Query dasar pasien nifas.
     */
    private function buildPasienNifasQuery(?string $q = '', ?string $faskesId = null)
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

            // ✅ FIX: pnb.bidan_id = puskesmas.id (klinik bidan)
            ->leftJoin('puskesmas as kb', 'kb.id', '=', 'pnb.bidan_id')

            // (opsional tapi aman) kalau suatu saat nama klinik ternyata di users.name,
            // ini bisa dipakai sebagai fallback (karena puskesmas biasanya punya user_id).
            ->leftJoin('users as ub', 'ub.id', '=', 'kb.user_id')



            ->leftJoin('pasien_nifas_rs as pnr', 'pnr.pasien_id', '=', 'p.id')
            // ✅ perbaikan menyeluruh: join rumah_sakits biar nama RS muncul
            ->leftJoin('rumah_sakits as rs', 'rs.id', '=', 'pnr.rs_id')

            // skrining terbaru (mengandung puskesmas induk)
            ->leftJoin(DB::raw($latestSkriningSql), 'ls.pasien_id', '=', 'p.id')
            // puskesmas asal skrining
            ->leftJoin('puskesmas as pk', 'pk.id', '=', 'ls.puskesmas_id')

            // ❗ Hanya pasien yang berdomisili di Depok (sesuai PKabupaten)
            ->whereRaw("COALESCE(p.\"PKabupaten\", '') ILIKE '%Depok%'")

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
                pk.nama_puskesmas as puskesmas_nama,

                CASE
                    WHEN pnb.id IS NOT NULL THEN
                        COALESCE(
                            NULLIF(kb.nama_puskesmas, ''),
                            NULLIF(ub.name, ''),
                            '-'
                        )
                    WHEN pnr.id IS NOT NULL THEN COALESCE(pk.nama_puskesmas, '-')
                    ELSE COALESCE(pk.nama_puskesmas, '-')
                END as faskes_nama,

                CASE
                    WHEN pnb.id IS NOT NULL THEN ('bidan:' || COALESCE(pnb.bidan_id::text, '0'))
                    WHEN pnr.id IS NOT NULL THEN ('puskesmas:' || COALESCE(pk.id::text, '0'))
                    ELSE ('puskesmas:' || COALESCE(pk.id::text, '0'))
                END as faskes_key,


                CASE
                    WHEN pnb.id IS NOT NULL THEN 'bidan'

                    -- ✅ RS dipaksa dianggap "puskesmas" untuk tujuan tampilan (agar output: "Puskesmas + [nama wilayah]")
                    WHEN pnr.id IS NOT NULL THEN 'puskesmas'

                    ELSE 'puskesmas'
                END as faskes_tipe
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

        /**
         * Filter faskes berdasarkan KEY "tipe:id"
         * contoh: puskesmas:12 / bidan:3 / rs:5
         */
        if (!empty($faskesId) && is_string($faskesId) && str_contains($faskesId, ':')) {
            [$type, $id] = explode(':', $faskesId, 2);
            $id = is_numeric($id) ? (int) $id : null;

            $query->where(function ($w) use ($type, $id) {
                if ($type === 'puskesmas' && $id) {
                    $w->where('pk.id', $id);
                } elseif ($type === 'bidan' && $id) {
                    // ✅ karena key bidan sekarang = klinik_id (puskesmas.id) yang ada di pnb.bidan_id
                    $w->where('pnb.bidan_id', $id);
                } elseif ($type === 'rs' && $id) {
                    $w->where('rs.id', $id);
                } else {
                    // key invalid -> jangan filter biar tidak blank total
                }
            });
        }

        return $query->orderBy('u.name');
    }


    /**
     * Export Excel (menghormati filter q + faskes_id + sort).
     */
    public function export(Request $request)
    {
        $q        = trim($request->get('q', ''));
        $faskesId = $request->get('faskes_id');
        $sort     = $request->get('sort', 'prioritas');
        $priority = $request->get('priority');

        // 1) Ambil data dasar pasien nifas (sama seperti index)
        $baseQuery = $this->buildPasienNifasQuery($q, $faskesId);
        $rows = $baseQuery->get();

        /**
         * 2) Hitung progres KF per pasien (pakai episode RS terbaru),
         *    sama persis dengan index()
         */
        $kfDone    = collect();
        $pasienIds = $rows->pluck('pasien_id')->filter()->values()->all();
        $rsEpisodeIds = [];

        if (!empty($pasienIds)) {
            $rsEpisodes = DB::table('pasien_nifas_rs')
                ->select('id', 'pasien_id', 'tanggal_mulai_nifas', 'tanggal_melahirkan', 'created_at')
                ->whereIn('pasien_id', $pasienIds)
                ->orderByDesc('created_at')
                ->get();

            $rsByPasien = [];
            foreach ($rsEpisodes as $ep) {
                if (!isset($rsByPasien[$ep->pasien_id])) {
                    $rsByPasien[$ep->pasien_id] = $ep;
                }
            }

            foreach ($rsByPasien as $pid => $ep) {
                if (!empty($ep->id)) {
                    $rsEpisodeIds[] = $ep->id;
                }
            }

            if (!empty($rsEpisodeIds)) {
                $kfByEpisode = DB::table('kf_kunjungans')
                    ->selectRaw('pasien_nifas_id, MAX(jenis_kf)::int as max_ke')
                    ->whereIn('pasien_nifas_id', $rsEpisodeIds)
                    ->groupBy('pasien_nifas_id')
                    ->get()
                    ->keyBy('pasien_nifas_id');

                $deathEpisodeIds = DB::table('kf_kunjungans as kk')
                    ->whereIn('kk.pasien_nifas_id', $rsEpisodeIds)
                    ->whereRaw("LOWER(COALESCE(kk.kesimpulan_pantauan,'')) IN ('meninggal','wafat')")
                    ->distinct()
                    ->pluck('kk.pasien_nifas_id')
                    ->toArray();

                $map = [];
                foreach ($rsByPasien as $pid => $ep) {
                    $episodeStat = $kfByEpisode->get($ep->id);
                    if ($episodeStat) {
                        $map[$pid] = (object) [
                            'max_ke'       => $episodeStat->max_ke,
                            'is_meninggal' => in_array($ep->id, $deathEpisodeIds, true),
                        ];
                    }
                }

                $kfDone = collect($map);
            }

            Log::debug('Dinkes Export Pasien Nifas - KF per pasien', [
                'total_rows'     => $rows->count(),
                'pasien_ids'     => $pasienIds,
                'rs_episode_ids' => $rsEpisodeIds,
            ]);
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
            return $this->hitungKfDanPrioritas($row, $kfDone, $dueDays, $today, $namaKf);
        });

        // Filter berdasarkan warna prioritas (opsional)
        if (!empty($priority)) {
            $priorityMap = [
                'hitam'        => 1,
                'merah'        => 2,
                'kuning'       => 3,
                'hijau'        => 4,
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

        // === BAGIAN STYLING EXCEL (tetap) ===
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
            'D' => 'Faskes',
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

            $sheet->setCellValue('D' . $rowIndex, $row->faskes_nama ?? '-');
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
     */
    private function hitungKfDanPrioritas($row, $kfDone, array $dueDays, Carbon $today, array $namaKf)
    {
        $pasienId = $row->pasien_id ?? null;

        $hasKf = $pasienId && $kfDone->has($pasienId);
        $info  = $hasKf ? $kfDone->get($pasienId) : null;

        $maxKe = $hasKf ? (int) ($info->max_ke ?? 0) : 0;
        $maxKe = max(0, min(4, $maxKe));

        $isMeninggal = $info && !empty($info->is_meninggal);

        $row->is_meninggal = $isMeninggal ? true : false;
        $row->max_kf_done  = $maxKe;

        if ($isMeninggal) {
            $row->next_kf_ke       = null;
            $row->jadwal_kf_date   = null;
            $row->hari_sisa        = null;

            $row->jadwal_kf_text    = 'Pemantauan dihentikan (status meninggal/wafat)';
            $row->sisa_waktu_label  = 'Pasien Meninggal';
            $row->badge_class       = 'bg-[#111827] text-white';
            $row->priority_level    = 5;

            return $row;
        }

        if ($maxKe >= 4) {
            $row->next_kf_ke       = null;
            $row->jadwal_kf_date   = null;
            $row->hari_sisa        = null;

            $row->jadwal_kf_text    = 'Seluruh kunjungan KF (KF1–KF4) sudah dilakukan';
            $row->sisa_waktu_label  = 'Selesai';
            $row->badge_class       = 'bg-[#E5E7EB] text-[#374151]';
            $row->priority_level    = 5;

            return $row;
        }

        $nextKe = $maxKe + 1;
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

        if ($jadwalDate) {
            $kfLabel = 'KF' . $nextKe;

            if ($hariSisa > 0) {
                $row->jadwal_kf_text = sprintf(
                    '%s akan dilakukan pada tanggal %s',
                    $kfLabel,
                    $jadwalDate->locale('id')->translatedFormat('d F Y')
                );
            } elseif ($hariSisa === 0) {
                $row->jadwal_kf_text = sprintf('Hari ini jadwal %s', $kfLabel);
            } else {
                $row->jadwal_kf_text = sprintf('%s sudah terlewat %d hari', $kfLabel, abs($hariSisa));
            }
        } else {
            $row->jadwal_kf_text = 'Jadwal KF belum tersedia';
        }

        if ($hariSisa === null) {
            $row->sisa_waktu_label = '—';
            $row->badge_class      = 'bg-[#E5E7EB] text-[#374151]';
            $row->priority_level   = 5;
        } else {
            if ($hariSisa >= 7) {
                $row->sisa_waktu_label = "Sisa {$hariSisa} Hari";
                $row->badge_class      = 'bg-[#2EDB58] text-white';
                $row->priority_level   = 4;
            } elseif ($hariSisa >= 4) {
                $row->sisa_waktu_label = "Sisa {$hariSisa} Hari";
                $row->badge_class      = 'bg-[#FFC400] text-[#1D1D1D]';
                $row->priority_level   = 3;
            } elseif ($hariSisa >= 0) {
                $row->sisa_waktu_label = "Sisa {$hariSisa} Hari";
                $row->badge_class      = 'bg-[#FF3B30] text-white';
                $row->priority_level   = 2;
            } else {
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
