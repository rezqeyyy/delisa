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

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ===================== 0. YEAR FILTER (KF CHART) =====================
        $selectedYear = (int) ($request->query('year') ?? now()->year);

        $availableYears = DB::table('kf')
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM tanggal_kunjungan)::int AS year')
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        // Subquery: skrining TERBARU per pasien (PostgreSQL DISTINCT ON)
        $latestSkriningSql = <<<SQL
            (
                SELECT DISTINCT ON (pasien_id)
                       id,
                       pasien_id,
                       puskesmas_id,
                       status_pre_eklampsia,
                       checked_status,
                       jumlah_resiko_sedang,
                       jumlah_resiko_tinggi,
                       created_at
                FROM skrinings
                ORDER BY pasien_id, created_at DESC
            ) AS ls
        SQL;

        // ===================== 1. ASAL PASIEN (DEPOK vs NON) =====================

        $asalDepok = DB::table('pasiens as p')
            ->whereRaw("COALESCE(p.\"PKabupaten\", '') ILIKE '%Depok%'")
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('skrinings as s')
                    ->whereColumn('s.pasien_id', 'p.id');
            })
            ->count();

        $asalNonDepok = DB::table('pasiens as p')
            ->whereRaw("(p.\"PKabupaten\" IS NULL OR p.\"PKabupaten\" NOT ILIKE '%Depok%')")
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('skrinings as s')
                    ->whereColumn('s.pasien_id', 'p.id');
            })
            ->count();

        // versi pendek (kalau masih dipakai di view lain)
        $depok = $asalDepok;
        $non   = $asalNonDepok;

        // ===================== 2. KF PER BULAN (12 SLOT) =====================

        $kfPerBulan = DB::table('kf')
            ->selectRaw('EXTRACT(MONTH FROM tanggal_kunjungan)::int as bulan, COUNT(*)::int as total')
            ->whereYear('tanggal_kunjungan', $selectedYear)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $seriesBulanan = array_fill(1, 12, 0);
        foreach ($kfPerBulan as $row) {
            $seriesBulanan[(int) $row->bulan] = (int) $row->total;
        }
        $seriesBulanan = array_values($seriesBulanan);

        // ===================== 3. RISIKO PRE-EKLAMPSIA (LATEST ONLY) =====================

        $resikoNormal = DB::query()
            ->from(DB::raw($latestSkriningSql))
            ->whereRaw("COALESCE(status_pre_eklampsia, '') ILIKE 'normal'")
            ->count();

        $resikoPreeklampsia = DB::query()
            ->from(DB::raw($latestSkriningSql))
            ->whereRaw("COALESCE(status_pre_eklampsia, '') NOT ILIKE 'normal'")
            ->count();

        // versi pendek
        $normal = $resikoNormal;
        $risk   = $resikoPreeklampsia;

        // ===================== 4. DATA NIFAS (TOTAL & SUDAH KFI) =====================

        // Total pasien nifas = distinct pasien_id dari dua sumber (bidan + rs)
        $unionNifas = DB::table('pasien_nifas_bidan')->select('pasien_id')
            ->union(DB::table('pasien_nifas_rs')->select('pasien_id'));

        $totalNifas = DB::query()
            ->fromSub($unionNifas, 't')
            ->distinct()
            ->count('pasien_id');

        // Sudah KFI = pasien yang punya minimal 4 kunjungan nifas (KF1-4)
        $sudahKFI = DB::table('kf')
            ->whereIn('kunjungan_nifas_ke', [1, 2, 3, 4])
            ->select('id_nifas')
            ->groupBy('id_nifas')
            ->havingRaw('COUNT(DISTINCT kunjungan_nifas_ke) >= 4')
            ->count();

        // ===================== 5. HADIR / MANGKIR (LATEST SKRINING) =====================

        $pasienHadir = DB::query()
            ->from(DB::raw($latestSkriningSql))
            ->where('checked_status', true)
            ->count();

        $pasienTidakHadir = DB::query()
            ->from(DB::raw($latestSkriningSql))
            ->where('checked_status', false)
            ->count();

        // versi pendek
        $hadir   = $pasienHadir;
        $mangkir = $pasienTidakHadir;

        // Absensi per bulan – dari tanggal skrining terbaru
        $absensiPerBulan = DB::query()
            ->from(DB::raw($latestSkriningSql))
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int as bulan, COUNT(*)::int as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $seriesAbsensi = array_values(array_replace(
            array_fill(1, 12, 0),
            $absensiPerBulan->pluck('total', 'bulan')->toArray()
        ));

        // ===================== 6. PEMANTAUAN KF =====================

        $pemantauanSehat = DB::table('kf')
            ->where('kesimpulan_pantauan', 'Sehat')
            ->count();

        $pemantauanDirujuk = DB::table('kf')
            ->where('kesimpulan_pantauan', 'Dirujuk')
            ->count();

        $pemantauanMeninggal = DB::table('kf')
            ->where('kesimpulan_pantauan', 'Meninggal')
            ->count();

        // versi pendek
        $sehat     = $pemantauanSehat;
        $dirujuk   = $pemantauanDirujuk;
        $meninggal = $pemantauanMeninggal;

        // ===================== 7. TABEL PE (LATEST PER PASIEN) =====================

        [$peQuery, $filters] = $this->buildPeQuery($request, $latestSkriningSql);

        $peList = $peQuery
            ->orderByDesc('ls.created_at')
            ->paginate(10)
            ->withQueryString();

        // ===================== 8. DAFTAR PUSKESMAS (UNTUK DROPDOWN) =====================

        $puskesmasList = DB::table('puskesmas')
            ->select('id', 'nama_puskesmas')
            ->orderBy('nama_puskesmas')
            ->get();

        // ===================== 9. RENDER VIEW =====================

        return view('dinkes.dasbor.dashboard', [
            // Asal pasien (versi baru + pendek)
            'asalDepok'   => $asalDepok,
            'asalNonDepok'=> $asalNonDepok,
            'depok'       => $depok,
            'non'         => $non,

            // Chart KF per bulan
            'seriesBulanan'  => $seriesBulanan,
            'selectedYear'   => $selectedYear,
            'availableYears' => $availableYears,

            // Nifas
            'totalNifas' => $totalNifas,
            'sudahKFI'   => $sudahKFI,

            // Risiko Pre-Eklampsia
            'resikoNormal'       => $resikoNormal,
            'resikoPreeklampsia' => $resikoPreeklampsia,
            'normal'             => $normal,
            'risk'               => $risk,

            // Hadir / Tidak Hadir
            'pasienHadir'      => $pasienHadir,
            'pasienTidakHadir' => $pasienTidakHadir,
            'hadir'            => $hadir,
            'mangkir'          => $mangkir,
            'seriesAbsensi'    => $seriesAbsensi,

            // Pemantauan
            'pemantauanSehat'     => $pemantauanSehat,
            'pemantauanDirujuk'   => $pemantauanDirujuk,
            'pemantauanMeninggal' => $pemantauanMeninggal,
            'sehat'               => $sehat,
            'dirujuk'             => $dirujuk,
            'meninggal'           => $meninggal,

            // Tabel PE
            'peList'        => $peList,
            'puskesmasList' => $puskesmasList,
            'filters'       => $filters,
        ]);
    }

    /**
     * Build query + data filter untuk tabel & export PE.
     */
    private function buildPeQuery(Request $request, string $latestSkriningSql): array
    {
        $q               = (string) $request->query('q', '');
        $from            = $request->query('from');
        $to              = $request->query('to');
        $resiko          = $request->query('resiko');
        $status          = $request->query('status');
        $kategori        = $request->query('kategori');
        $puskesmasId     = $request->query('puskesmas_id');
        $riwayatSelected = (array) $request->query('riwayat_penyakit_ui', []);

        $peQuery = DB::query()
            ->from(DB::raw($latestSkriningSql))
            ->join('pasiens as p', 'p.id', '=', 'ls.pasien_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('kondisi_kesehatans as kk', 'kk.skrining_id', '=', 'ls.id')
            ->leftJoin('puskesmas as pk', 'pk.id', '=', 'ls.puskesmas_id')
            ->selectRaw("
                ls.id AS skrining_id,
                p.id AS pasien_id,

                -- identitas dasar
                u.name AS nama,
                u.phone,
                p.nik,
                p.tempat_lahir,
                p.tanggal_lahir,

                -- alamat / fasilitas
                pk.nama_puskesmas AS puskesmas,
                p.\"PWilayah\"   AS kelurahan,
                p.\"PKecamatan\" AS kecamatan,
                p.\"PKabupaten\" AS kabupaten,
                p.\"PProvinsi\"  AS provinsi,

                CASE
                    WHEN length(p.nik) = 16
                        THEN substr(p.nik,1,4) || '•••' || substr(p.nik,13,4)
                    ELSE p.nik
                END AS nik_masked,

                EXTRACT(YEAR FROM age(current_date, p.tanggal_lahir))::int AS umur,
                kk.usia_kehamilan,
                to_char(ls.created_at, 'DD/MM/YYYY') AS tanggal,
                ls.checked_status AS status_hadir,
                ls.jumlah_resiko_sedang,
                ls.jumlah_resiko_tinggi,
                CASE
                    WHEN ls.jumlah_resiko_tinggi > 0 THEN 'tinggi'
                    WHEN ls.jumlah_resiko_sedang > 0 THEN 'sedang'
                    ELSE 'non-risk'
                END AS resiko
            ");

        // ---- Search bebas (nama / NIK)
        if ($q !== '') {
            $like = '%' . str_replace('%', '\%', $q) . '%';
            $peQuery->where(function ($w) use ($like) {
                $w->whereRaw('u.name ILIKE ?', [$like])
                    ->orWhereRaw('p.nik ILIKE ?', [$like]);
            });
        }

        // ---- Rentang tanggal
        if ($from) {
            $peQuery->whereRaw('ls.created_at::date >= ?', [$from]);
        }
        if ($to) {
            $peQuery->whereRaw('ls.created_at::date <= ?', [$to]);
        }

        // ---- Filter resiko
        if ($resiko === 'tinggi') {
            $peQuery->whereRaw('COALESCE(ls.jumlah_resiko_tinggi,0) > 0');
        } elseif ($resiko === 'sedang') {
            $peQuery->whereRaw('COALESCE(ls.jumlah_resiko_tinggi,0) = 0')
                ->whereRaw('COALESCE(ls.jumlah_resiko_sedang,0) > 0');
        } elseif ($resiko === 'non-risk') {
            $peQuery->whereRaw('COALESCE(ls.jumlah_resiko_tinggi,0) = 0')
                ->whereRaw('COALESCE(ls.jumlah_resiko_sedang,0) = 0');
        }

        // ---- Filter status hadir
        if ($status === 'hadir') {
            $peQuery->whereRaw('COALESCE(ls.checked_status, false) = true');
        } elseif ($status === 'mangkir') {
            $peQuery->whereRaw('COALESCE(ls.checked_status, false) = false');
        }

        // ---- Filter puskesmas
        if (is_numeric($puskesmasId)) {
            $peQuery->where('ls.puskesmas_id', (int) $puskesmasId);
        }

        // ---- Filter Kategori (remaja, JKN, asuransi, domisili, BB)
        switch ($kategori) {
            case 'remaja': // < 20 tahun
                $peQuery->whereRaw(
                    'EXTRACT(YEAR FROM age(current_date, p.tanggal_lahir))::int < 20'
                );
                break;

            case 'jkn':
                $peQuery->where(function ($w) {
                    $w->whereRaw("COALESCE(p.pembiayaan_kesehatan,'') ILIKE '%jkn%'")
                        ->orWhereRaw("NULLIF(TRIM(COALESCE(p.no_jkn,'')), '') IS NOT NULL");
                });
                break;

            case 'asuransi':
                $peQuery->whereRaw("COALESCE(p.pembiayaan_kesehatan,'') ILIKE '%asuransi%'");
                break;

            case 'depok':
                $peQuery->whereRaw("COALESCE(p.\"PKabupaten\", '') ILIKE '%Depok%'");
                break;

            case 'non_depok':
                $peQuery->whereRaw("(p.\"PKabupaten\" IS NULL OR p.\"PKabupaten\" NOT ILIKE '%Depok%')");
                break;

            case 'bb_normal':
                $peQuery->whereRaw("COALESCE(kk.status_imt,'') ILIKE '%normal%'");
                break;

            case 'bb_kurang':
                $peQuery->where(function ($w) {
                    $w->whereRaw("LOWER(kk.status_imt) LIKE '%kurus%'")
                        ->orWhereRaw("LOWER(kk.status_imt) LIKE '%under%'");
                });
                break;
        }

        // ---- Filter RIWAYAT PENYAKIT (multi-select)
        $riwayatSelected = array_filter($riwayatSelected); // buang string kosong kalau ada

        if (!empty($riwayatSelected)) {
            $rpMap = [
                'hipertensi'  => 'Hipertensi',
                'alergi'      => 'Alergi',
                'tiroid'      => 'Tiroid',
                'tb'          => 'TB',
                'jantung'     => 'Jantung',
                'hepatitis_b' => 'Hepatitis B',
                'jiwa'        => 'Jiwa',
                'autoimun'    => 'Autoimun',
                'sifilis'     => 'Sifilis',
                'diabetes'    => 'Diabetes',
                'asma'        => 'Asma',
                'lainnya'     => 'Lainnya',
            ];

            $namaPertanyaan = [];
            $filterLainnya  = false;

            foreach ($riwayatSelected as $code) {
                if ($code === 'lainnya') {
                    $filterLainnya = true;
                    continue;
                }
                if (isset($rpMap[$code])) {
                    $namaPertanyaan[] = $rpMap[$code];
                }
            }

            $peQuery->whereExists(function ($q) use ($namaPertanyaan, $filterLainnya) {
                $q->select(DB::raw(1))
                    ->from('jawaban_kuisioners as jk')
                    ->join('kuisioner_pasiens as kp', 'kp.id', '=', 'jk.kuisioner_id')
                    ->whereColumn('jk.skrining_id', 'ls.id')
                    ->where('kp.status_soal', 'individu')
                    ->where(function ($w) use ($namaPertanyaan, $filterLainnya) {
                        if (!empty($namaPertanyaan)) {
                            $w->where(function ($w2) use ($namaPertanyaan) {
                                $w2->whereIn('kp.nama_pertanyaan', $namaPertanyaan)
                                    ->where('jk.jawaban', true);
                            });
                        }

                        if ($filterLainnya) {
                            $w->orWhere(function ($w2) {
                                $w2->where('kp.nama_pertanyaan', 'Lainnya')
                                    ->where('jk.jawaban', true)
                                    ->whereRaw("NULLIF(TRIM(COALESCE(jk.jawaban_lainnya,'')), '') IS NOT NULL");
                            });
                        }
                    });
            });
        }

        $filters = [
            'q'                   => $q,
            'from'                => $from,
            'to'                  => $to,
            'resiko'              => $resiko,
            'status'              => $status,
            'kategori'            => $kategori,
            'puskesmas_id'        => $puskesmasId,
            'riwayat_penyakit_ui' => $riwayatSelected,
        ];

        return [$peQuery, $filters];
    }

    /**
     * EXPORT: Unduh semua data pasien PE (sesuai filter) dalam bentuk .xlsx
     * dengan styling mirip template contoh.
     */
    public function exportPe(Request $request)
    {
        $latestSkriningSql = <<<SQL
            (
                SELECT DISTINCT ON (pasien_id)
                       id,
                       pasien_id,
                       puskesmas_id,
                       status_pre_eklampsia,
                       checked_status,
                       jumlah_resiko_sedang,
                       jumlah_resiko_tinggi,
                       created_at
                FROM skrinings
                ORDER BY pasien_id, created_at DESC
            ) AS ls
        SQL;

        [$peQuery,] = $this->buildPeQuery($request, $latestSkriningSql);

        $rows = $peQuery
            ->orderByDesc('ls.created_at')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // ========== 1. Judul ==========
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', 'Laporan Data Pasien Keseluruhan');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Baris 2 kosong
        $sheet->getRowDimension(2)->setRowHeight(5);

        // ========== 2. Header ==========
        $headerRow = 3;
        $headers   = [
            'A' => 'ID Skrining',
            'B' => 'Nama Lengkap',
            'C' => 'NIK',
            'D' => 'Nomor Handphone',
            'E' => 'Tempat Lahir',
            'F' => 'Tanggal Lahir',
            'G' => 'Puskesmas',
            'H' => 'Kelurahan',
            'I' => 'Kecamatan',
            'J' => 'Kabupaten',
            'K' => 'Provinsi',
            'L' => 'Jumlah Resiko Sedang',
            'M' => 'Jumlah Resiko Tinggi',
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . $headerRow, $text);
        }

        $headerRange = 'A' . $headerRow . ':M' . $headerRow;
        $sheet->getStyle($headerRange)->getFont()
            ->setBold(true)
            ->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4F81BD');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(13);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(18);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(20);

        // NIK & HP sebagai text
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('@');

        // ========== 3. Data ==========
        $rowIndex = $headerRow + 1;

        foreach ($rows as $row) {
            $tglLahir = $row->tanggal_lahir
                ? Carbon::parse($row->tanggal_lahir)->format('d-m-Y')
                : '';

            $sheet->setCellValue('A' . $rowIndex, $row->skrining_id);
            $sheet->setCellValue('B' . $rowIndex, $row->nama);
            $sheet->setCellValueExplicit('C' . $rowIndex, $row->nik, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowIndex, $row->phone, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowIndex, $row->tempat_lahir);
            $sheet->setCellValue('F' . $rowIndex, $tglLahir);
            $sheet->setCellValue('G' . $rowIndex, $row->puskesmas);
            $sheet->setCellValue('H' . $rowIndex, $row->kelurahan);
            $sheet->setCellValue('I' . $rowIndex, $row->kecamatan);
            $sheet->setCellValue('J' . $rowIndex, $row->kabupaten);
            $sheet->setCellValue('K' . $rowIndex, $row->provinsi);
            $sheet->setCellValue('L' . $rowIndex, $row->jumlah_resiko_sedang ?? 0);
            $sheet->setCellValue('M' . $rowIndex, $row->jumlah_resiko_tinggi ?? 0);

            $rowIndex++;
        }

        $lastDataRow = $rowIndex - 1;
        if ($lastDataRow < $headerRow) {
            $lastDataRow = $headerRow;
        }

        $tableRange = 'A' . $headerRow . ':M' . $lastDataRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        for ($r = $headerRow; $r <= $lastDataRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        $sheet->freezePane('A' . ($headerRow + 1));

        $fileName = 'data-pasien-keseluruhan-' . now()->format('Y-m-d') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
