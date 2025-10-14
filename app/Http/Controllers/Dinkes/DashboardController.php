<?php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // === 1) Daerah Asal Pasien (Depok vs Non Depok) ===
        $depok = DB::table('pasiens')
            ->whereRaw("COALESCE(\"PKabupaten\", '') ILIKE '%Depok%'")
            ->count();

        $non = DB::table('pasiens')
            ->whereRaw("(\"PKabupaten\" IS NULL OR \"PKabupaten\" NOT ILIKE '%Depok%')")
            ->count();

        // Series bulanan dari tabel KF (selalu 12 slot: Jan–Des)
        $kfPerBulan = DB::table('kf')
            ->selectRaw('EXTRACT(MONTH FROM tanggal_kunjungan)::int as bulan, COUNT(*)::int as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $seriesBulanan = array_fill(1, 12, 0);
        foreach ($kfPerBulan as $row) {
            $seriesBulanan[(int)$row->bulan] = (int)$row->total;
        }
        // Blade kamu akses pakai foreach 12 item -> kirim index 0..11
        $seriesBulanan = array_values($seriesBulanan);

        // === 2) Risiko Pre-Eklampsia ===
        $normal = DB::table('skrinings')
            ->whereRaw("COALESCE(status_pre_eklampsia, '') ILIKE 'normal'")
            ->count();

        $risk = DB::table('skrinings')
            ->whereRaw("COALESCE(status_pre_eklampsia, '') NOT ILIKE 'normal'")
            ->count();

        // === 3) Data Pasien Nifas ===
        $totalNifas = DB::table('pasiens')->count();

        $kf1 = DB::table('kf')->where('kunjungan_nifas_ke', 1)->count();
        $kf2 = DB::table('kf')->where('kunjungan_nifas_ke', 2)->count();
        $kf3 = DB::table('kf')->where('kunjungan_nifas_ke', 3)->count();
        $kf4 = DB::table('kf')->where('kunjungan_nifas_ke', 4)->count();

        // === 4) Pasien Hadir (pakai checked_status) ===
        $hadir   = DB::table('skrinings')->where('checked_status', true)->count();
        $mangkir = DB::table('skrinings')->where('checked_status', false)->count();

        // Series absensi per bulan dari created_at skrinings (selalu 12 slot)
        $absensiPerBulan = DB::table('skrinings')
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int as bulan, COUNT(*)::int as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $seriesAbsensi = array_fill(1, 12, 0);
        foreach ($absensiPerBulan as $row) {
            $seriesAbsensi[(int)$row->bulan] = (int)$row->total;
        }
        $seriesAbsensi = array_values($seriesAbsensi);

        // === 5) Pemantauan (enum di kf_kesimpulan_pantauan_enum) ===
        $sehat     = DB::table('kf')->where('kesimpulan_pantauan', 'Sehat')->count();
        $dirujuk   = DB::table('kf')->where('kesimpulan_pantauan', 'Dirujuk')->count();
        $meninggal = DB::table('kf')->where('kesimpulan_pantauan', 'Meninggal')->count();

        // === 6) Data tabel "Pasien Pre-Eklampsia" (opsional, view siap fallback) ===
        // join users untuk nama, join kondisi_kesehatans untuk usia kehamilan
        $peList = DB::table('skrinings as s')
            ->join('pasiens as p', 'p.id', '=', 's.pasien_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('kondisi_kesehatans as kk', 'kk.skrining_id', '=', 's.id')
            ->selectRaw("
                u.name AS nama,
                p.nik,
                CASE 
                    WHEN length(p.nik) = 16 
                        THEN substr(p.nik,1,4) || '•••' || substr(p.nik,13,4)
                    ELSE p.nik
                END AS nik_masked,
                EXTRACT(YEAR FROM age(current_date, p.tanggal_lahir))::int AS umur,
                kk.usia_kehamilan,
                to_char(s.created_at, 'DD/MM/YYYY') AS tanggal,
                s.checked_status AS status_hadir,
                s.jumlah_resiko_sedang,
                s.jumlah_resiko_tinggi,
                CASE 
                    WHEN s.jumlah_resiko_tinggi > 0 THEN 'tinggi'
                    WHEN s.jumlah_resiko_sedang > 0 THEN 'sedang'
                    ELSE 'non-risk'
                END AS resiko
            ")
            ->orderByDesc('s.created_at')
            ->limit(10)
            ->get();

        return view('dinkes.dashboard', [
            'depok'          => $depok,
            'non'            => $non,
            'seriesBulanan'  => $seriesBulanan,

            'normal'         => $normal,
            'risk'           => $risk,

            'totalNifas'     => $totalNifas,
            'kf1'            => $kf1,
            'kf2'            => $kf2,
            'kf3'            => $kf3,
            'kf4'            => $kf4,

            'hadir'          => $hadir,
            'mangkir'        => $mangkir,
            'seriesAbsensi'  => $seriesAbsensi,

            'sehat'          => $sehat,
            'dirujuk'        => $dirujuk,
            'meninggal'      => $meninggal,

            'peList'         => $peList, // tabel di view akan otomatis pakai ini jika ada
        ]);
    }
}
