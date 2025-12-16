<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Pasien;
use App\Models\Skrining;
use App\Models\Kf;
use App\Models\Bidan;
use App\Models\PasienNifasBidan;
use App\Http\Controllers\Pasien\skrining\Concerns\SkriningHelpers;

/*
|--------------------------------------------------------------------------
| DASHBOARD CONTROLLER
|--------------------------------------------------------------------------
| Fungsi: Mengelola halaman dashboard Bidan
| Menampilkan: Statistik skrining preeklampsia & data pasien nifas
|--------------------------------------------------------------------------
*/

class DashboardController extends Controller
{
    use SkriningHelpers;
    /*
    |--------------------------------------------------------------------------
    | METHOD: index()
    |--------------------------------------------------------------------------
    | Fungsi: Menampilkan dashboard dengan semua statistik
    | Return: View 'bidan.dashboard' dengan data cards & tabel
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        // 1. Validasi Bidan Login
        $bidan = Auth::user()->bidan; // Ambil data bidan dari user login
        if (!$bidan) { // Jika user bukan bidan
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }
        $puskesmasId = $bidan->puskesmas_id; // ID puskesmas bidan untuk filter data

        // 2. Ambil semua skrining milik puskesmas dan filter yang lengkap
        $skrinings = Skrining::where('puskesmas_id', $puskesmasId)
            ->whereHas('puskesmas', function ($q) {
                $q->where('is_mandiri', true);
            })
            ->with(['pasien.user', 'riwayatKehamilanGpa', 'kondisiKesehatan'])
            ->latest()
            ->get()
            ->filter(fn($s) => $this->isSkriningCompleteForSkrining($s))
            ->values();

        // 3. Card: Daerah Asal Pasien (Depok vs Non-Depok) dari skrining lengkap
        $pasienIds = $skrinings->pluck('pasien_id')->unique();
        $pasienList = Pasien::whereIn('id', $pasienIds)->get(['PKabupaten']);
        $depok = $pasienList->filter(function ($p) {
            $kab = mb_strtolower(trim($p->PKabupaten ?? ''));
            return $kab !== '' && strpos($kab, 'depok') !== false;
        })->count();
        $nonDepok = $pasienList->count() - $depok;
        $daerahAsal = (object) ['depok' => $depok, 'non_depok' => $nonDepok];

        // 4. Card: Risiko Preeklampsia (Normal vs Berisiko) dari skrining lengkap
        $resikoBeresiko = $skrinings->filter(function ($s) {
            $label = strtolower(trim($s->kesimpulan ?? ''));
            return in_array($label, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi']);
        })->count();
        $resikoNormal = $skrinings->count() - $resikoBeresiko;

        // 5. Card: Pasien Hadir (Hadir Hari Ini vs Tidak Hadir) dari skrining lengkap
        $pasienHadir = $skrinings->filter(fn($s) => optional($s->updated_at)->isToday())->count();
        $pasienTidakHadir = $skrinings->count() - $pasienHadir;

        // =====================
        // 6-9. STATISTIK NIFAS (SAMAKAN DENGAN HALAMAN PASIEN NIFAS BIDAN)
        // =====================

        $pasienNifasRows = DB::table('pasien_nifas_bidan')
            ->where('bidan_id', $puskesmasId)
            ->select('id', 'pasien_id')
            ->get();

        // Total pasien nifas (episode di bidan)
        $totalNifas = $pasienNifasRows->count();

        // Ambil latest episode RS per pasien (karena KF disimpan di kf_kunjungans berbasis pasien_nifas_rs.id)
        $pasienIdsForNifas = $pasienNifasRows->pluck('pasien_id')->unique()->values()->all();

        $rsEpisodes = DB::table('pasien_nifas_rs')
            ->select('id', 'pasien_id', 'created_at')
            ->whereIn('pasien_id', $pasienIdsForNifas)
            ->orderByDesc('created_at')
            ->get();

        // map latest RS episode id per pasien_id
        $latestRsEpisodeIdByPasien = [];
        foreach ($rsEpisodes as $ep) {
            if (!isset($latestRsEpisodeIdByPasien[$ep->pasien_id])) {
                $latestRsEpisodeIdByPasien[$ep->pasien_id] = $ep->id;
            }
        }

        $rsEpisodeIds = array_values(array_filter($latestRsEpisodeIdByPasien));
        $rsEpisodeIds = array_values(array_unique($rsEpisodeIds));
        // Fallback: sebagian data KF (tergantung implementasi/migrasi) bisa nyantol ke episode bidan
        $bidanEpisodeIds = $pasienNifasRows->pluck('id')->filter()->values()->all();

        // Gabungkan kandidat id episode untuk cek kf_kunjungans (RS + Bidan)
        $kfEpisodeIds = array_values(array_unique(array_merge($rsEpisodeIds, $bidanEpisodeIds)));

        $sudahKf1 = 0;
        if (!empty($kfEpisodeIds)) {
            $sudahKf1 = DB::table('kf_kunjungans')
                ->whereIn('pasien_nifas_id', $kfEpisodeIds)
                ->where('jenis_kf', 1)
                ->distinct('pasien_nifas_id')
                ->count('pasien_nifas_id');
        }


        // Belum KF1
        $belumKf1 = max(0, $totalNifas - $sudahKf1);

        // Pemantauan nifas (Sehat/Dirujuk/Meninggal) — hitung distinct pasien_nifas_id
        $pemantauanSehat = 0;
        $pemantauanDirujuk = 0;
        $pemantauanMeninggal = 0;

        if (!empty($kfEpisodeIds)) {
            $pemantauanSehat = DB::table('kf_kunjungans')
                ->whereIn('pasien_nifas_id', $kfEpisodeIds)
                ->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'sehat'")
                ->distinct('pasien_nifas_id')
                ->count('pasien_nifas_id');

            $pemantauanDirujuk = DB::table('kf_kunjungans')
                ->whereIn('pasien_nifas_id', $kfEpisodeIds)
                ->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) = 'dirujuk'")
                ->distinct('pasien_nifas_id')
                ->count('pasien_nifas_id');

            $pemantauanMeninggal = DB::table('kf_kunjungans')
                ->whereIn('pasien_nifas_id', $kfEpisodeIds)
                ->whereRaw("LOWER(TRIM(kesimpulan_pantauan)) IN ('meninggal','wafat')")
                ->distinct('pasien_nifas_id')
                ->count('pasien_nifas_id');
        }



        // 10. Tabel: 5 Data Skrining Terbaru dari skrining lengkap
        $pasienTerbaru = $skrinings->sortByDesc('created_at')->take(5)->values();

        // 11. Kirim Data ke View
        // compact(): ubah variable jadi array ['daerahAsal' => $daerahAsal, ...]
        return view('bidan.dashboard', compact(
            'daerahAsal',
            'resikoNormal',
            'resikoBeresiko',
            'pasienHadir',
            'pasienTidakHadir',
            'totalNifas',
            'sudahKf1',
            'belumKf1',
            'pemantauanSehat',
            'pemantauanDirujuk',
            'pemantauanMeninggal',
            'pasienTerbaru'
        ));
    }
}

/*
|--------------------------------------------------------------------------
| PENJELASAN DETAIL FUNGSI-FUNGSI:
|--------------------------------------------------------------------------
|
| 1. Auth::user()->bidan
|    - Ambil user yang login
|    - Akses relasi bidan() di Model User
|    - Return: object Bidan atau null
|
| 2. abort(403, 'pesan')
|    - Stop eksekusi controller
|    - Tampilkan error 403 Forbidden
|    - Digunakan untuk validasi akses
|
| 3. clone $query
|    - Duplikasi query tanpa mengubah query asli
|    - Bisa pakai query yang sama berulang kali
|    - Contoh: (clone $skriningsQuery)->count()
|
| 4. pluck('column')
|    - Ambil hanya 1 kolom dari database
|    - Return: Collection [nilai1, nilai2, ...]
|    - Lebih cepat dari select()
|
| 5. selectRaw()
|    - Menjalankan raw SQL query
|    - Untuk agregasi kompleks (SUM, CASE WHEN)
|    - Return: object dengan property sesuai alias (as nama)
|
| 6. whereIn('column', array)
|    - Filter data yang kolom-nya ada di array
|    - SQL: WHERE column IN (val1, val2, ...)
|    - Contoh: whereIn('id', [1,2,3])
|
| 7. distinct('column')
|    - Ambil nilai unik dari kolom
|    - Hindari duplikasi data
|    - Biasanya dipakai dengan count()
|
| 8. with(['relasi'])
|    - Eager loading relasi
|    - Hindari N+1 query problem
|    - Load semua relasi sekaligus, lebih efisien
|
| 9. latest()
|    - Urutkan berdasarkan created_at DESC
|    - Data terbaru di atas
|    - Sama dengan: orderBy('created_at', 'desc')
|
| 10. take(n)
|     - Limit hasil query
|     - Ambil n data teratas
|     - Sama dengan: limit(n)
|
| 11. compact('var1', 'var2')
|     - Ubah variable jadi array asosiatif
|     - ['var1' => $var1, 'var2' => $var2]
|     - Untuk passing data ke view
|
|--------------------------------------------------------------------------
*/