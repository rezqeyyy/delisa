<?php

/**
 * Controller untuk dashboard Dinkes:
 * - Menghitung statistik pasien, skrining, nifas, absensi, risiko PE, dsb.
 * - Menyediakan query builder untuk tabel & export pasien Preeklampsia.
 * - Menyediakan export ke .xlsx dengan styling tertentu.
 */

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;

// Model
use App\Models\Pasien;
use App\Models\Skrining;
use App\Models\PasienNifasBidan;
use App\Models\PasienNifasRs;
use App\Models\Puskesmas;
use App\Models\KfKunjungan;

// HTTP & DB
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


// Tanggal
use Carbon\Carbon;

// Excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DashboardController extends Controller
{
    /**
     * Halaman utama dashboard Dinkes.
     */
    public function index(Request $request)
    {
        // ===================== 0. YEAR FILTER (KF CHART) =====================

        $selectedYear = (int) ($request->query('year') ?? now()->year);

        // Tahun yang tersedia berdasarkan tanggal_kunjungan KF
        $availableYears = KfKunjungan::query()
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM tanggal_kunjungan)::int AS year')
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        // Subquery skrining terbaru per pasien
        $latestSkriningSql = <<<SQL
            (
                SELECT DISTINCT ON (pasien_id)
                       id,
                       pasien_id,
                       puskesmas_id,
                       status_pre_eklampsia,
                       checked_status,
                       jumlah_resiko_tinggi,
                       created_at
                FROM skrinings
                ORDER BY pasien_id, created_at DESC
            ) AS ls
        SQL;

        // ===================== 1. ASAL PASIEN (DEPOK vs NON) =====================

        $asalDepok = Pasien::query()
            ->from('pasiens as p')
            ->whereRaw("COALESCE(p.\"PKabupaten\", '') ILIKE '%Depok%'")
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('skrinings as s')
                    ->whereColumn('s.pasien_id', 'p.id');
            })
            ->count();

        $asalNonDepok = Pasien::query()
            ->from('pasiens as p')
            ->whereRaw("(p.\"PKabupaten\" IS NULL OR p.\"PKabupaten\" NOT ILIKE '%Depok%')")
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('skrinings as s')
                    ->whereColumn('s.pasien_id', 'p.id');
            })
            ->count();

        $depok = $asalDepok;
        $non   = $asalNonDepok;

        // ===================== 2. KUNJUNGAN NIFAS PER BULAN =====================
        // Dihitung per PASIEN nifas warga Depok yang punya kunjungan KF
        // (bukan jumlah baris KF mentah)

        $kfPerBulan = KfKunjungan::query()
            ->join('pasien_nifas_rs as pnr', 'pnr.id', '=', 'kf_kunjungans.pasien_nifas_id')
            ->join('pasiens as p', 'p.id', '=', 'pnr.pasien_id')
            ->selectRaw('
                EXTRACT(MONTH FROM kf_kunjungans.tanggal_kunjungan)::int AS bulan,
                COUNT(DISTINCT p.id)::int AS total
            ')
            ->whereYear('kf_kunjungans.tanggal_kunjungan', $selectedYear)
            ->whereRaw('COALESCE(p."PKabupaten", \'\') ILIKE \'%Depok%\'')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // isi 12 slot bulan (1–12), default 0
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

        $normal = $resikoNormal;
        $risk   = $resikoPreeklampsia;

        // ===================== 4. DATA NIFAS (TOTAL & SUDAH KFI) =====================

        // Gabungkan semua episode nifas (bidan + RS)
        $unionNifas = PasienNifasBidan::select('pasien_id')
            ->union(
                PasienNifasRs::select('pasien_id')
            );

        // Ambil daftar pasien_id nifas yang berdomisili di Depok saja
        $pasienNifasDepokIds = DB::query()
            ->fromSub($unionNifas, 't')
            ->join('pasiens as p', 'p.id', '=', 't.pasien_id')
            ->whereRaw("COALESCE(p.\"PKabupaten\", '') ILIKE '%Depok%'")
            ->distinct()
            ->pluck('t.pasien_id')
            ->toArray();

        $totalNifas = count($pasienNifasDepokIds);

        // Sudah KFI = episode nifas RS yang punya minimal 1 KF
        // dan PASIEN-nya warga Depok
        $sudahKFI = 0;

        if (!empty($pasienNifasDepokIds)) {
            $sudahKFI = KfKunjungan::query()
                ->join('pasien_nifas_rs as pnr', 'pnr.id', '=', 'kf_kunjungans.pasien_nifas_id')
                ->join('pasiens as p', 'p.id', '=', 'pnr.pasien_id')
                ->whereIn('pnr.pasien_id', $pasienNifasDepokIds)
                ->whereRaw("COALESCE(p.\"PKabupaten\", '') ILIKE '%Depok%'")
                ->distinct('kf_kunjungans.pasien_nifas_id')
                ->count('kf_kunjungans.pasien_nifas_id');
        }

        // Debug singkat supaya bisa dipantau di log
        Log::debug('Dashboard Dinkes - Statistik Nifas (Depok saja)', [
            'total_nifas_depok' => $totalNifas,
            'sudah_kfi_depok'   => $sudahKFI,
            'pasien_ids_depok'  => $pasienNifasDepokIds,
        ]);


        // ===================== 5. HADIR / MANGKIR (LATEST SKRINING) =====================

        $totalPasienTerdaftar = Pasien::count();

        $pasienHadir = Skrining::query()
            ->distinct('pasien_id')
            ->count('pasien_id');

        $pasienTidakHadir = $totalPasienTerdaftar - $pasienHadir;

        $hadir   = $pasienHadir;
        $mangkir = $pasienTidakHadir;

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

        // ===================== 6. PEMANTAUAN KF (STATUS TERAKHIR PER PASIEN) =====================
        // Pakai subquery latest KF per pasien_nifas_id, lalu hitung jumlah pasien Sehat / Dirujuk / Meninggal

        $latestKfSql = <<<SQL
            (
                SELECT DISTINCT ON (pasien_nifas_id)
                       id,
                       pasien_nifas_id,
                       kesimpulan_pantauan,
                       tanggal_kunjungan,
                       created_at
                FROM kf_kunjungans
                ORDER BY pasien_nifas_id, tanggal_kunjungan DESC, created_at DESC
            ) AS lkf
        SQL;

        // Hanya hitung pemantauan untuk pasien nifas RS yang PASIEN-nya warga Depok
        $basePemantauanQuery = DB::query()
            ->from(DB::raw($latestKfSql))
            ->join('pasien_nifas_rs as pnr', 'pnr.id', '=', 'lkf.pasien_nifas_id')
            ->join('pasiens as p', 'p.id', '=', 'pnr.pasien_id')
            ->whereRaw("COALESCE(p.\"PKabupaten\", '') ILIKE '%Depok%'");

        $pemantauanSehat = (clone $basePemantauanQuery)
            ->where('kesimpulan_pantauan', 'Sehat')
            ->count();

        $pemantauanDirujuk = (clone $basePemantauanQuery)
            ->where('kesimpulan_pantauan', 'Dirujuk')
            ->count();

        $pemantauanMeninggal = (clone $basePemantauanQuery)
            ->where('kesimpulan_pantauan', 'Meninggal')
            ->count();


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

        $puskesmasList = Puskesmas::query()
            ->select('id', 'nama_puskesmas')
            ->orderBy('nama_puskesmas')
            ->get();

        // ===================== 9. RENDER VIEW =====================

        return view('dinkes.dasbor.dashboard', [
            // Asal pasien
            'asalDepok'    => $asalDepok,
            'asalNonDepok' => $asalNonDepok,
            'depok'        => $depok,
            'non'          => $non,

            // Chart KF per bulan (jumlah pasien nifas yang punya KF di bulan tsb)
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

            // Pemantauan KF (berdasarkan status TERAKHIR per pasien nifas)
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
     * Helper untuk membangun query PE (Preeklampsia) beserta filters:
     * - search by nama/NIK
     * - rentang tanggal
     * - filter risiko
     * - filter status hadir
     * - filter kategori (remaja, JKN, asuransi, domisili, bb)
     * - filter puskesmas
     * - filter riwayat penyakit
     *
     * Mengembalikan array [Builder $peQuery, array $filters]
     */
    private function buildPeQuery(Request $request, string $latestSkriningSql): array
    {
        // Ambil semua parameter filter dari query string (atau default)
        $q               = (string) $request->query('q', '');
        $from            = $request->query('from');
        $to              = $request->query('to');
        $resiko          = $request->query('resiko');
        $status          = $request->query('status');
        $kategori        = $request->query('kategori');
        $puskesmasId     = $request->query('puskesmas_id');
        // riwayat_penyakit_ui diambil sebagai array (multi-select)
        $riwayatSelected = (array) $request->query('riwayat_penyakit_ui', []);

        // Mulai query dari subquery latest skrining: ls
        $peQuery = DB::query()
            ->from(DB::raw($latestSkriningSql))
            // Join pasien agar bisa akses data demografi
            ->join('pasiens as p', 'p.id', '=', 'ls.pasien_id')
            // Join users untuk nama, phone, dsb.
            ->join('users as u', 'u.id', '=', 'p.user_id')
            // Left join kondisi_kesehatans (bisa null)
            ->leftJoin('kondisi_kesehatans as kk', 'kk.skrining_id', '=', 'ls.id')
            // Left join puskesmas agar bisa tampil nama puskesmas
            ->leftJoin('puskesmas as pk', 'pk.id', '=', 'ls.puskesmas_id')
            // Select raw untuk membentuk struktur data yang lengkap
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

                -- NIK yang dimasking untuk tampilan (jika dipakai)
                CASE
                    WHEN length(p.nik) = 16
                        THEN substr(p.nik,1,4) || '•••' || substr(p.nik,13,4)
                    ELSE p.nik
                END AS nik_masked,

                -- umur pasien (tahun) dari tanggal lahir (0 / negatif dianggap tidak valid)
                CASE
                    WHEN p.tanggal_lahir IS NULL THEN NULL
                    WHEN EXTRACT(YEAR FROM age(current_date, p.tanggal_lahir))::int <= 0 THEN NULL
                    ELSE EXTRACT(YEAR FROM age(current_date, p.tanggal_lahir))::int
                END AS umur,


                -- usia kehamilan dari tabel kondisi_kesehatans (jika ada)
                kk.usia_kehamilan,

                -- tanggal skrining dalam format DD/MM/YYYY
                to_char(ls.created_at, 'DD/MM/YYYY') AS tanggal,

                -- status hadir (checked_status)
                ls.checked_status AS status_hadir,

                -- jumlah risiko tinggi
                ls.jumlah_resiko_tinggi,

                -- kategori resiko gabungan (beresiko/non-risk)
                CASE
                    WHEN ls.jumlah_resiko_tinggi > 0 THEN 'tinggi'
                    ELSE 'non-risk'
                END AS resiko
            ");

        // ---- Search bebas (nama / NIK)
        if ($q !== '') {
            // Escape '%', lalu bungkus dengan %...%
            $like = '%' . str_replace('%', '\%', $q) . '%';

            $peQuery->where(function ($w) use ($like) {
                // ILIKE untuk case-insensitive di PostgreSQL
                $w->whereRaw('u.name ILIKE ?', [$like])
                    ->orWhereRaw('p.nik ILIKE ?', [$like]);
            });
        }

        // ---- Rentang tanggal 'from' (tanggal skrining >= from)
        if ($from) {
            $peQuery->whereRaw('ls.created_at::date >= ?', [$from]);
        }

        // ---- Rentang tanggal 'to' (tanggal skrining <= to)
        if ($to) {
            $peQuery->whereRaw('ls.created_at::date <= ?', [$to]);
        }

        // ---- Filter resiko berbasis jumlah_resiko_tinggi
        if ($resiko === 'tinggi') {
            // Any risiko tinggi > 0
            $peQuery->whereRaw('COALESCE(ls.jumlah_resiko_tinggi,0) > 0');
        } elseif ($resiko === 'non-risk') {
            // Tidak ada risiko 
            $peQuery->whereRaw('COALESCE(ls.jumlah_resiko_tinggi,0) = 0');
        }

        // ---- Filter status hadir (hadir/mangkir)
        if ($status === 'hadir') {
            // hadir jika checked_status = true
            $peQuery->whereRaw('COALESCE(ls.checked_status, false) = true');
        } elseif ($status === 'mangkir') {
            // mangkir jika checked_status = false
            $peQuery->whereRaw('COALESCE(ls.checked_status, false) = false');
        }

        // ---- Filter puskesmas (id numerik)
        if (is_numeric($puskesmasId)) {
            $peQuery->where('ls.puskesmas_id', (int) $puskesmasId);
        }

        // ---- Filter Kategori (remaja, JKN, asuransi, domisili, BB)
        switch ($kategori) {
            case 'remaja': // umur < 20 tahun
                $peQuery->whereRaw(
                    'EXTRACT(YEAR FROM age(current_date, p.tanggal_lahir))::int < 20'
                );
                break;

            case 'jkn':
                $peQuery->where(function ($w) {
                    // pembiayaan_kesehatan mengandung JKN atau no_jkn tidak kosong
                    $w->whereRaw("COALESCE(p.pembiayaan_kesehatan,'') ILIKE '%jkn%'")
                        ->orWhereRaw("NULLIF(TRIM(COALESCE(p.no_jkn,'')), '') IS NOT NULL");
                });
                break;

            case 'asuransi':
                // pembiayaan_kesehatan mengandung kata 'asuransi'
                $peQuery->whereRaw("COALESCE(p.pembiayaan_kesehatan,'') ILIKE '%asuransi%'");
                break;

            case 'depok':
                // domisili kabupaten mengandung 'Depok'
                $peQuery->whereRaw("COALESCE(p.\"PKabupaten\", '') ILIKE '%Depok%'");
                break;

            case 'non_depok':
                // bukan domisili Depok
                $peQuery->whereRaw("(p.\"PKabupaten\" IS NULL OR p.\"PKabupaten\" NOT ILIKE '%Depok%')");
                break;

            case 'bb_normal':
                // status_imt mengandung 'normal'
                $peQuery->whereRaw("COALESCE(kk.status_imt,'') ILIKE '%normal%'");
                break;

            case 'bb_kurang':
                // status_imt mengandung kata 'kurus' atau 'under' (underweight)
                $peQuery->where(function ($w) {
                    $w->whereRaw("LOWER(kk.status_imt) LIKE '%kurus%'")
                        ->orWhereRaw("LOWER(kk.status_imt) LIKE '%under%'");
                });
                break;
        }

        // ---- Filter RIWAYAT PENYAKIT (multi-select)
        // Bersihkan array dari string kosong
        $riwayatSelected = array_filter($riwayatSelected);

        if (!empty($riwayatSelected)) {
            // Pemetaan kode UI -> teks nama_pertanyaan di kuisioner_pasiens
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

            // Array untuk menampung nama pertanyaan yang dipilih (selain 'lainnya')
            $namaPertanyaan = [];
            // Flag apakah filter 'lainnya' juga dipilih
            $filterLainnya  = false;

            // Loop semua kode riwayat yang dipilih dari UI
            foreach ($riwayatSelected as $code) {
                // Jika pilih 'lainnya', set flag dan lanjut
                if ($code === 'lainnya') {
                    $filterLainnya = true;
                    continue;
                }

                // Jika kode ada di peta, tambahkan ke daftar nama_pertanyaan
                if (isset($rpMap[$code])) {
                    $namaPertanyaan[] = $rpMap[$code];
                }
            }

            // whereExists: hanya ambil skrining yang punya jawaban penyakit individu sesuai filter
            $peQuery->whereExists(function ($q) use ($namaPertanyaan, $filterLainnya) {
                $q->select(DB::raw(1))
                    ->from('jawaban_kuisioners as jk')
                    ->join('kuisioner_pasiens as kp', 'kp.id', '=', 'jk.kuisioner_id')
                    // Hubungkan jawaban kuisioner ke skrining ls
                    ->whereColumn('jk.skrining_id', 'ls.id')
                    // status_soal = individu berarti kuisioner individu (penyakit, dsb)
                    ->where('kp.status_soal', 'individu')
                    // Bungkus kondisi nama penyakit & lainnya
                    ->where(function ($w) use ($namaPertanyaan, $filterLainnya) {
                        // Jika ada beberapa penyakit spesifik dipilih
                        if (!empty($namaPertanyaan)) {
                            $w->where(function ($w2) use ($namaPertanyaan) {
                                // nama_pertanyaan ada di list dan jawaban = true
                                $w2->whereIn('kp.nama_pertanyaan', $namaPertanyaan)
                                    ->where('jk.jawaban', true);
                            });
                        }

                        // Jika user juga memilih 'lainnya'
                        if ($filterLainnya) {
                            $w->orWhere(function ($w2) {
                                $w2->where('kp.nama_pertanyaan', 'Lainnya')
                                    ->where('jk.jawaban', true)
                                    // jawaban_lainnya tidak kosong (ada isinya)
                                    ->whereRaw("NULLIF(TRIM(COALESCE(jk.jawaban_lainnya,'')), '') IS NOT NULL");
                            });
                        }
                    });
            });
        }

        // Susun kembali semua filter yang dipakai agar bisa dikirim ke view
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

        // Kembalikan query builder & filters untuk dipakai di index() atau exportPe()
        return [$peQuery, $filters];
    }

    /**
     * EXPORT: Unduh semua data pasien PE (sesuai filter) dalam bentuk .xlsx
     * dengan styling mirip template contoh yang sudah Kamu pakai.
     */
    public function exportPe(Request $request)
    {
        // Subquery latest skrining per pasien, sama seperti di index()
        $latestSkriningSql = <<<SQL
            (
                SELECT DISTINCT ON (pasien_id)
                       id,
                       pasien_id,
                       puskesmas_id,
                       status_pre_eklampsia,
                       checked_status,
                       jumlah_resiko_tinggi,
                       created_at
                FROM skrinings
                ORDER BY pasien_id, created_at DESC
            ) AS ls
        SQL;

        // Build query PE dengan filter yang sama seperti di tabel dashboard
        [$peQuery,] = $this->buildPeQuery($request, $latestSkriningSql);

        // Ambil semua baris tanpa pagination (karena ini untuk export)
        $rows = $peQuery
            ->orderByDesc('ls.created_at')
            ->get();

        // Buat objek Spreadsheet baru
        $spreadsheet = new Spreadsheet();
        // Ambil sheet aktif (default: Sheet1)
        $sheet       = $spreadsheet->getActiveSheet();

        // ========== 1. Judul ==========

        // Merge sel A1 sampai M1 untuk judul besar
        $sheet->mergeCells('A1:M1');
        // Set teks judul
        $sheet->setCellValue('A1', 'Laporan Data Pasien Keseluruhan');
        // Bold + ukuran font 14 untuk judul
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        // Center horizontal dan vertical
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        // Tinggi baris 1 sedikit lebih besar untuk judul
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Baris 2 kosong (spasi antar judul dan header tabel)
        $sheet->getRowDimension(2)->setRowHeight(5);

        // ========== 2. Header ==========

        // Nomor baris header tabel
        $headerRow = 3;

        // Daftar header kolom dan labelnya
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
            'L' => 'Jumlah Resiko Tinggi',
        ];

        // Isi teks header di row 3
        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . $headerRow, $text);
        }

        // Range header (A3:M3)
        $headerRange = 'A' . $headerRow . ':L' . $headerRow;

        // Style header: bold + tulisan berwarna putih
        $sheet->getStyle($headerRange)->getFont()
            ->setBold(true)
            ->getColor()->setARGB('FFFFFFFF');

        // Background header warna solid (biru tua 4F81BD)
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4F81BD');

        // Header rata tengah
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set lebar tiap kolom agar tabel rapi
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

        // Pastikan kolom NIK & Nomor HP diperlakukan sebagai text (bukan angka)
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('@');

        // ========== 3. Data ==========

        // Mulai baris data setelah header (row 4)
        $rowIndex = $headerRow + 1;

        // Loop semua baris hasil query
        foreach ($rows as $row) {
            // Format tanggal lahir menjadi d-m-Y jika tidak null
            $tglLahir = $row->tanggal_lahir
                ? Carbon::parse($row->tanggal_lahir)->format('d-m-Y')
                : '';

            // Isi masing-masing kolom dengan data dari query
            $sheet->setCellValue('A' . $rowIndex, $row->skrining_id);
            $sheet->setCellValue('B' . $rowIndex, $row->nama);

            // NIK harus explicit text (TYPE_STRING) agar tidak hilang leading zero
            $sheet->setCellValueExplicit(
                'C' . $rowIndex,
                $row->nik,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );

            // Nomor HP juga explicit text
            $sheet->setCellValueExplicit(
                'D' . $rowIndex,
                $row->phone,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );

            $sheet->setCellValue('E' . $rowIndex, $row->tempat_lahir);
            $sheet->setCellValue('F' . $rowIndex, $tglLahir);
            $sheet->setCellValue('G' . $rowIndex, $row->puskesmas);
            $sheet->setCellValue('H' . $rowIndex, $row->kelurahan);
            $sheet->setCellValue('I' . $rowIndex, $row->kecamatan);
            $sheet->setCellValue('J' . $rowIndex, $row->kabupaten);
            $sheet->setCellValue('K' . $rowIndex, $row->provinsi);
            $sheet->setCellValue('L' . $rowIndex, $row->jumlah_resiko_tinggi ?? 0);

            // Naikkan index baris untuk data berikutnya
            $rowIndex++;
        }

        // Baris terakhir yang berisi data
        $lastDataRow = $rowIndex - 1;

        // Jika ternyata tidak ada data (lastDataRow < headerRow) set minimal sama dengan headerRow
        if ($lastDataRow < $headerRow) {
            $lastDataRow = $headerRow;
        }

        // Range seluruh tabel dari header hingga baris terakhir data
        $tableRange = 'A' . $headerRow . ':M' . $lastDataRow;

        // Beri border tipis di seluruh tabel
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Atur tinggi baris dari header sampai baris terakhir agar konsisten
        for ($r = $headerRow; $r <= $lastDataRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        // Freeze pane: kunci baris di atas row data pertama agar header ikut scroll
        $sheet->freezePane('A' . ($headerRow + 1));

        // Nama file unduhan, contoh: data-pasien-keseluruhan-2025-11-19.xlsx
        $fileName = 'data-pasien-keseluruhan-' . now()->format('Y-m-d') . '.xlsx';

        // Buat writer Xlsx dari Spreadsheet
        $writer = new Xlsx($spreadsheet);

        // Kembalikan response streamDownload supaya file langsung diunduh ke browser
        return response()->streamDownload(function () use ($writer) {
            // Simpan output ke php://output (stream)
            $writer->save('php://output');
        }, $fileName, [
            // Set content-type sesuai file Excel modern
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Hapus satu data skrining (baris di tabel PE) beserta relasinya.
     * Dipakai untuk menghapus data dummy / tidak valid dari dashboard Dinkes.
     */
    public function destroyPe(Request $request, int $skriningId)
    {
        try {
            DB::transaction(function () use ($skriningId) {
                // Karena semua relasi ke skrinings.* sudah ON DELETE CASCADE,
                // cukup hapus di tabel skrinings saja.
                Skrining::whereKey($skriningId)->delete();
            });

            return redirect()
                ->back()
                ->with('success', 'Data skrining berhasil dihapus.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data skrining.');
        }
    }
}
