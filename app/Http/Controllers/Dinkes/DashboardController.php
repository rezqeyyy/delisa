<?php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ====== Tahun utk chart batang KF ======
        $selectedYear = (int) ($request->query('year') ?? now()->year);

        $availableYears = DB::table('kf')
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM tanggal_kunjungan)::int AS year')
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();
        if (empty($availableYears)) $availableYears = [now()->year];

        // === 1) Daerah Asal Pasien (Depok vs Non Depok) ===
        $depok = DB::table('pasiens')
            ->whereRaw("COALESCE(\"PKabupaten\", '') ILIKE '%Depok%'")
            ->count();

        $non = DB::table('pasiens')
            ->whereRaw("(\"PKabupaten\" IS NULL OR \"PKabupaten\" NOT ILIKE '%Depok%')")
            ->count();

        // ===== Series bulanan KF (12 slot) — by tahun =====
        $kfPerBulan = DB::table('kf')
            ->selectRaw('EXTRACT(MONTH FROM tanggal_kunjungan)::int as bulan, COUNT(*)::int as total')
            ->whereYear('tanggal_kunjungan', $selectedYear)
            ->groupBy('bulan')->orderBy('bulan')->get();

        $seriesBulanan = array_fill(1, 12, 0);
        foreach ($kfPerBulan as $row) $seriesBulanan[(int)$row->bulan] = (int)$row->total;
        $seriesBulanan = array_values($seriesBulanan);

        // === 2) Risiko Pre-Eklampsia ===
        $normal = DB::table('skrinings')
            ->whereRaw("COALESCE(status_pre_eklampsia, '') ILIKE 'normal'")->count();

        $risk = DB::table('skrinings')
            ->whereRaw("COALESCE(status_pre_eklampsia, '') NOT ILIKE 'normal'")->count();

        // ========== 3) Data Pasien Nifas (DONUT) — filter Bulan & Tahun ==========
        $dkfMonth = $request->query('dkf_month'); // 1..12 | null
        $dkfYear  = $request->query('dkf_year');  // int | null

        $dkfMonth = is_numeric($dkfMonth) && (int)$dkfMonth >= 1 && (int)$dkfMonth <= 12 ? (int)$dkfMonth : null;
        $dkfYear  = is_numeric($dkfYear)  ? (int)$dkfYear  : null;

        $isDonutFiltered = !is_null($dkfYear) || !is_null($dkfMonth);

        if ($isDonutFiltered) {
            // Basis periode: kf as k + batasi hanya episode nifas yang valid
            $kfPeriod = DB::table('kf as k');
            if (!is_null($dkfYear))  $kfPeriod->whereYear('k.tanggal_kunjungan', $dkfYear);
            if (!is_null($dkfMonth)) $kfPeriod->whereMonth('k.tanggal_kunjungan', $dkfMonth);

            // Total pasien unik pada periode: join ke episode nifas (bidan/rs) & pastikan salah satu ada
            $totalNifas = (clone $kfPeriod)
                ->leftJoin('pasien_nifas_bidan as pnb', 'pnb.id', '=', 'k.id_nifas')
                ->leftJoin('pasien_nifas_rs as pnr',   'pnr.id', '=', 'k.id_nifas')
                ->where(function ($w) {
                    $w->whereNotNull('pnb.pasien_id')
                      ->orWhereNotNull('pnr.pasien_id');
                })
                ->selectRaw('COUNT(DISTINCT COALESCE(pnb.pasien_id, pnr.pasien_id)) as total')
                ->value('total');

            // KF1..KF4 pada periode (tetap dari kf pada periode tsb)
            $kf1 = (clone $kfPeriod)->where('k.kunjungan_nifas_ke', 1)->count();
            $kf2 = (clone $kfPeriod)->where('k.kunjungan_nifas_ke', 2)->count();
            $kf3 = (clone $kfPeriod)->where('k.kunjungan_nifas_ke', 3)->count();
            $kf4 = (clone $kfPeriod)->where('k.kunjungan_nifas_ke', 4)->count();
        } else {
            // RESET: hanya pasien yang memang nifas (gabungan bidan/rs), bukan semua pasien
            $union = DB::table('pasien_nifas_bidan')->select('pasien_id')
                ->union(DB::table('pasien_nifas_rs')->select('pasien_id'));

            $totalNifas = DB::query()
                ->fromSub($union, 't')
                ->distinct()
                ->count('pasien_id');

            // KF1..KF4 total (semua periode)
            $kf1 = DB::table('kf')->where('kunjungan_nifas_ke', 1)->count();
            $kf2 = DB::table('kf')->where('kunjungan_nifas_ke', 2)->count();
            $kf3 = DB::table('kf')->where('kunjungan_nifas_ke', 3)->count();
            $kf4 = DB::table('kf')->where('kunjungan_nifas_ke', 4)->count();
        }

        // === 4) Pasien Hadir / Mangkir ===
        $hadir   = DB::table('skrinings')->where('checked_status', true)->count();
        $mangkir = DB::table('skrinings')->where('checked_status', false)->count();

        $absensiPerBulan = DB::table('skrinings')
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int as bulan, COUNT(*)::int as total')
            ->groupBy('bulan')->orderBy('bulan')->get();

        $seriesAbsensi = array_fill(1, 12, 0);
        foreach ($absensiPerBulan as $row) $seriesAbsensi[(int)$row->bulan] = (int)$row->total;
        $seriesAbsensi = array_values($seriesAbsensi);

        // === 5) Pemantauan ===
        $sehat     = DB::table('kf')->where('kesimpulan_pantauan', 'Sehat')->count();
        $dirujuk   = DB::table('kf')->where('kesimpulan_pantauan', 'Dirujuk')->count();
        $meninggal = DB::table('kf')->where('kesimpulan_pantauan', 'Meninggal')->count();

        // ================== 6) FILTER UNTUK TABEL PE ==================
        $q      = (string) $request->query('q', '');
        $from   = $request->query('from');
        $to     = $request->query('to');
        $resiko = $request->query('resiko');
        $status = $request->query('status');

        $peQuery = DB::table('skrinings as s')
            ->join('pasiens as p', 'p.id', '=', 's.pasien_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('kondisi_kesehatans as kk', 'kk.skrining_id', '=', 's.id')
            ->selectRaw("
                s.id AS skrining_id,
                p.id AS pasien_id,
                u.name AS nama,
                p.nik,
                CASE WHEN length(p.nik) = 16
                    THEN substr(p.nik,1,4) || '•••' || substr(p.nik,13,4)
                    ELSE p.nik END AS nik_masked,
                EXTRACT(YEAR FROM age(current_date, p.tanggal_lahir))::int AS umur,
                kk.usia_kehamilan,
                to_char(s.created_at, 'DD/MM/YYYY') AS tanggal,
                s.checked_status AS status_hadir,
                s.jumlah_resiko_sedang,
                s.jumlah_resiko_tinggi,
                CASE
                    WHEN s.jumlah_resiko_tinggi > 0 THEN 'tinggi'
                    WHEN s.jumlah_resiko_sedang > 0 THEN 'sedang'
                    ELSE 'non-risk' END AS resiko
            ");

        if ($q !== '') {
            $like = '%' . str_replace('%', '\%', $q) . '%';
            $peQuery->where(function ($w) use ($like) {
                $w->whereRaw('u.name ILIKE ?', [$like])
                  ->orWhereRaw('p.nik ILIKE ?', [$like]);
            });
        }
        if ($from) $peQuery->whereRaw('s.created_at::date >= ?', [$from]);
        if ($to)   $peQuery->whereRaw('s.created_at::date <= ?', [$to]);

        if ($resiko === 'tinggi') {
            $peQuery->whereRaw('COALESCE(s.jumlah_resiko_tinggi,0) > 0');
        } elseif ($resiko === 'sedang') {
            $peQuery->whereRaw('COALESCE(s.jumlah_resiko_tinggi,0) = 0')
                    ->whereRaw('COALESCE(s.jumlah_resiko_sedang,0) > 0');
        } elseif ($resiko === 'non-risk') {
            $peQuery->whereRaw('COALESCE(s.jumlah_resiko_tinggi,0) = 0')
                    ->whereRaw('COALESCE(s.jumlah_resiko_sedang,0) = 0');
        }

        if ($status === 'hadir') {
            $peQuery->whereRaw('COALESCE(s.checked_status, false) = true');
        } elseif ($status === 'mangkir') {
            $peQuery->whereRaw('COALESCE(s.checked_status, false) = false');
        }

        $peList = $peQuery->orderByDesc('s.created_at')
            ->limit(100)
            ->get();

            

        return view('dinkes.dasbor.dashboard', [
            'depok'          => $depok,
            'non'            => $non,

            // chart batang KF
            'seriesBulanan'  => $seriesBulanan,
            'selectedYear'   => $selectedYear,
            'availableYears' => $availableYears,

            // donut data pasien nifas (hanya pasien yang melakukan pemantauan nifas)
            'totalNifas' => $totalNifas,
            'kf1' => $kf1, 'kf2' => $kf2, 'kf3' => $kf3, 'kf4' => $kf4,
            'dkfMonth' => $dkfMonth, 'dkfYear' => $dkfYear,
            'isDonutFiltered' => $isDonutFiltered,

            // lainnya
            'normal' => $normal, 'risk' => $risk,
            'hadir' => $hadir, 'mangkir' => $mangkir,
            'seriesAbsensi' => $seriesAbsensi,
            'sehat' => $sehat, 'dirujuk' => $dirujuk, 'meninggal' => $meninggal,
            'peList' => $peList,

            // state filter tabel PE
            'filters' => [
                'q' => $q, 'from' => $from, 'to' => $to,
                'resiko' => $resiko, 'status' => $status,
            ],
        ]);
    }
}
