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


class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard Bidan.
     */
    public function index()
    {
        // 1. Dapatkan Bidan yang sedang login dan Puskesmas-nya
        // Pastikan di Model User ada relasi: public function bidan() { return $this->hasOne(Bidan::class); }
        $bidan = Auth::user()->bidan;
        if (!$bidan) {
            // Handle jika user bukan bidan atau relasi tidak terdefinisi
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }
        $puskesmasId = $bidan->puskesmas_id;

        // 2. Query dasar untuk pasien & skrining di Puskesmas ini
        $skriningsQuery = Skrining::where('puskesmas_id', $puskesmasId);
        $pasienIds = (clone $skriningsQuery)->pluck('pasien_id');
        $pasienQuery = Pasien::whereIn('id', $pasienIds);

        // 3. Card: Daerah Asal Pasien (Depok/Non Depok)
        // Menggunakan "PKabupaten" dari tabel pasiens
        $daerahAsal = (clone $pasienQuery)->selectRaw(
            'SUM(CASE WHEN "PKabupaten" = \'Depok\' THEN 1 ELSE 0 END) as depok,
             SUM(CASE WHEN "PKabupaten" != \'Depok\' OR "PKabupaten" IS NULL THEN 1 ELSE 0 END) as non_depok'
        )->first();

        // 4. Card: Resiko Eklampsia (Normal/Beresiko)
        // Menggunakan "kesimpulan" dari tabel skrinings
        $resiko = (clone $skriningsQuery)->selectRaw(
            'SUM(CASE WHEN kesimpulan = \'Normal\' THEN 1 ELSE 0 END) as normal,
             SUM(CASE WHEN kesimpulan = \'Beresiko\' THEN 1 ELSE 0 END) as beresiko'
        )->first();

        // 5. Card: Pasien Hadir (Hadir/Tidak Hadir)
        // Asumsi "Hadir" = skrining di-update hari ini, "Tidak Hadir" = sisanya
        $totalSkrining = (clone $skriningsQuery)->count();
        $pasienHadir = (clone $skriningsQuery)->whereDate('updated_at', today())->count();
        $pasienTidakHadir = $totalSkrining - $pasienHadir;

        // --- Data Nifas (Terkait dengan Bidan di Puskesmas) ---
        
        // 6. Dapatkan semua ID Bidan di Puskesmas yg sama
        $bidanIdsPuskesmas = Bidan::where('puskesmas_id', $puskesmasId)->pluck('id');
        
        // 7. Dapatkan semua ID Pasien Nifas yg ditangani Bidan-bidan tsb
        $pasienNifasIds = PasienNifasBidan::whereIn('bidan_id', $bidanIdsPuskesmas)->pluck('pasien_id');

        // 8. Card: Data Pasien Nifas (Total/Sudah KF1)
        $totalNifas = $pasienNifasIds->count();
        $sudahKf1 = Kf::whereIn('id_nifas', $pasienNifasIds)
                      ->where('kunjungan_nifas_ke', '>=', 1)
                      ->distinct('id_nifas')
                      ->count();

        // 9. Card: Pemantauan (Sehat/Dirujuk/Meninggal)
        // Mengambil status unik per pasien dari kunjungan KF
        $pemantauanSehat = Kf::whereIn('id_nifas', $pasienNifasIds)
                             ->where('kesimpulan_pantauan', 'Sehat')
                             ->distinct('id_nifas')
                             ->count();
        $pemantauanDirujuk = Kf::whereIn('id_nifas', $pasienNifasIds)
                               ->where('kesimpulan_pantauan', 'Dirujuk')
                               ->distinct('id_nifas')
                               ->count();
        $pemantauanMeninggal = Kf::whereIn('id_nifas', $pasienNifasIds)
                                 ->where('kesimpulan_pantauan', 'Meninggal')
                                 ->distinct('id_nifas')
                                 ->count();

        // 10. Table: Data Pasien Pre Eklampsia (5 Terbaru)
        // Asumsi relasi: Skrining->pasien(), Pasien->user()
        $pasienTerbaru = (clone $skriningsQuery)
                            ->with(['pasien.user']) // Eager load relasi
                            ->latest() // Urutkan berdasarkan created_at terbaru
                            ->take(5)
                            ->get();
        
        // 11. Kirim semua data ke View
        return view('bidan.dashboard', compact(
            'daerahAsal',
            'resiko',
            'pasienHadir',
            'pasienTidakHadir',
            'totalNifas',
            'sudahKf1',
            'pemantauanSehat',
            'pemantauanDirujuk',
            'pemantauanMeninggal',
            'pasienTerbaru'
        ));
    }
}