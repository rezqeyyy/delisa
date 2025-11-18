<?php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $depok = DB::table('pasiens as p')
            ->whereRaw("COALESCE(p.\"PKabupaten\", '') ILIKE '%Depok%'")
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('skrinings as s')
                    ->whereColumn('s.pasien_id', 'p.id');
            })
            ->count();

        $non = DB::table('pasiens as p')
            ->whereRaw("(p.\"PKabupaten\" IS NULL OR p.\"PKabupaten\" NOT ILIKE '%Depok%')")
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('skrinings as s')
                    ->whereColumn('s.pasien_id', 'p.id');
            })
            ->count();

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

        $normal = DB::query()
            ->from(DB::raw($latestSkriningSql))
            ->whereRaw("COALESCE(status_pre_eklampsia, '') ILIKE 'normal'")
            ->count();

        $risk = DB::query()
            ->from(DB::raw($latestSkriningSql))
            ->whereRaw("COALESCE(status_pre_eklampsia, '') NOT ILIKE 'normal'")
            ->count();

        // ===================== 4. DONUT NIFAS (KF) =====================

        $dkfMonth = $request->query('dkf_month'); // 1..12 | null
        $dkfYear  = $request->query('dkf_year');  // int | null

        $dkfMonth = is_numeric($dkfMonth) && (int) $dkfMonth >= 1 && (int) $dkfMonth <= 12
            ? (int) $dkfMonth
            : null;

        $dkfYear  = is_numeric($dkfYear)
            ? (int) $dkfYear
            : null;

        $isDonutFiltered = !is_null($dkfYear) || !is_null($dkfMonth);

        if ($isDonutFiltered) {
            $kfPeriod = DB::table('kf as k');

            if (!is_null($dkfYear)) {
                $kfPeriod->whereYear('k.tanggal_kunjungan', $dkfYear);
            }
            if (!is_null($dkfMonth)) {
                $kfPeriod->whereMonth('k.tanggal_kunjungan', $dkfMonth);
            }

            $totalNifas = (clone $kfPeriod)
                ->leftJoin('pasien_nifas_bidan as pnb', 'pnb.id', '=', 'k.id_nifas')
                ->leftJoin('pasien_nifas_rs as pnr', 'pnr.id', '=', 'k.id_nifas')
                ->where(function ($w) {
                    $w->whereNotNull('pnb.pasien_id')
                      ->orWhereNotNull('pnr.pasien_id');
                })
                ->selectRaw('COUNT(DISTINCT COALESCE(pnb.pasien_id, pnr.pasien_id)) as total')
                ->value('total');

            $kf1 = (clone $kfPeriod)->where('k.kunjungan_nifas_ke', 1)->count();
            $kf2 = (clone $kfPeriod)->where('k.kunjungan_nifas_ke', 2)->count();
            $kf3 = (clone $kfPeriod)->where('k.kunjungan_nifas_ke', 3)->count();
            $kf4 = (clone $kfPeriod)->where('k.kunjungan_nifas_ke', 4)->count();
        } else {
            $union = DB::table('pasien_nifas_bidan')->select('pasien_id')
                ->union(DB::table('pasien_nifas_rs')->select('pasien_id'));

            $totalNifas = DB::query()
                ->fromSub($union, 't')
                ->distinct()
                ->count('pasien_id');

            $kf1 = DB::table('kf')->where('kunjungan_nifas_ke', 1)->count();
            $kf2 = DB::table('kf')->where('kunjungan_nifas_ke', 2)->count();
            $kf3 = DB::table('kf')->where('kunjungan_nifas_ke', 3)->count();
            $kf4 = DB::table('kf')->where('kunjungan_nifas_ke', 4)->count();
        }

        // ===================== 5. HADIR / MANGKIR (LATEST) =====================

        $hadir = DB::query()
            ->from(DB::raw($latestSkriningSql))
            ->where('checked_status', true)
            ->count();

        $mangkir = DB::query()
            ->from(DB::raw($latestSkriningSql))
            ->where('checked_status', false)
            ->count();

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

        $sehat     = DB::table('kf')->where('kesimpulan_pantauan', 'Sehat')->count();
        $dirujuk   = DB::table('kf')->where('kesimpulan_pantauan', 'Dirujuk')->count();
        $meninggal = DB::table('kf')->where('kesimpulan_pantauan', 'Meninggal')->count();

        // ===================== 7. TABEL PE (LATEST PER PASIEN) =====================

        $q           = (string) $request->query('q', '');
        $from        = $request->query('from');
        $to          = $request->query('to');
        $resiko      = $request->query('resiko');
        $status      = $request->query('status');
        $kategori    = $request->query('kategori');
        $puskesmasId = $request->query('puskesmas_id');

        $peQuery = DB::query()
            ->from(DB::raw($latestSkriningSql))
            ->join('pasiens as p', 'p.id', '=', 'ls.pasien_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('kondisi_kesehatans as kk', 'kk.skrining_id', '=', 'ls.id')
            ->selectRaw("
                ls.id AS skrining_id,
                p.id AS pasien_id,
                u.name AS nama,
                p.nik,
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

        // ---- Paginate
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
            'depok'          => $depok,
            'non'            => $non,

            'seriesBulanan'  => $seriesBulanan,
            'selectedYear'   => $selectedYear,
            'availableYears' => $availableYears,

            'totalNifas'     => $totalNifas,
            'kf1'            => $kf1,
            'kf2'            => $kf2,
            'kf3'            => $kf3,
            'kf4'            => $kf4,
            'dkfMonth'       => $dkfMonth,
            'dkfYear'        => $dkfYear,
            'isDonutFiltered'=> $isDonutFiltered,

            'normal'         => $normal,
            'risk'           => $risk,
            'hadir'          => $hadir,
            'mangkir'        => $mangkir,
            'seriesAbsensi'  => $seriesAbsensi,
            'sehat'          => $sehat,
            'dirujuk'        => $dirujuk,
            'meninggal'      => $meninggal,

            'peList'         => $peList,
            'puskesmasList'  => $puskesmasList,

            'filters'        => [
                'q'            => $q,
                'from'         => $from,
                'to'           => $to,
                'resiko'       => $resiko,
                'status'       => $status,
                'kategori'     => $kategori,
                'puskesmas_id' => $puskesmasId,
            ],
        ]);
    }
}
