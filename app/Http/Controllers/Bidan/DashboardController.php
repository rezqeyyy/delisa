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

        // 2. Query Dasar Skrining & Pasien
        $skriningsQuery = Skrining::where('puskesmas_id', $puskesmasId); // Filter skrining per puskesmas
        $pasienIds = (clone $skriningsQuery)->pluck('pasien_id'); // Ambil semua ID pasien
        $pasienQuery = Pasien::whereIn('id', $pasienIds); // Query pasien berdasarkan ID

        // 3. Card: Daerah Asal Pasien (Depok vs Non-Depok)
        // Hitung jumlah pasien berdasarkan kolom PKabupaten
        $daerahAsal = (clone $pasienQuery)->selectRaw(
            'SUM(CASE WHEN "PKabupaten" = \'Depok\' THEN 1 ELSE 0 END) as depok,
             SUM(CASE WHEN "PKabupaten" != \'Depok\' OR "PKabupaten" IS NULL THEN 1 ELSE 0 END) as non_depok'
        )->first(); // Return: object dengan property depok & non_depok

        // 4. Card: Risiko Eklampsia (Normal vs Beresiko)
        // Hitung jumlah skrining berdasarkan kesimpulan
        $resiko = (clone $skriningsQuery)->selectRaw(
            'SUM(CASE WHEN kesimpulan = \'Normal\' THEN 1 ELSE 0 END) as normal,
             SUM(CASE WHEN kesimpulan = \'Beresiko\' THEN 1 ELSE 0 END) as beresiko'
        )->first(); // Return: object dengan property normal & beresiko

        // 5. Card: Pasien Hadir (Hadir Hari Ini vs Tidak Hadir)
        // Asumsi: Hadir = skrining di-update hari ini
        $totalSkrining = (clone $skriningsQuery)->count(); // Total semua skrining
        $pasienHadir = (clone $skriningsQuery)->whereDate('updated_at', today())->count(); // Update hari ini
        $pasienTidakHadir = $totalSkrining - $pasienHadir; // Selisih = tidak hadir

        // 6. Data Nifas - Ambil ID Bidan di Puskesmas yang Sama
        $bidanIdsPuskesmas = Bidan::where('puskesmas_id', $puskesmasId)->pluck('id'); // Semua bidan di puskesmas ini
        
        // 7. Ambil ID Pasien Nifas yang Ditangani Bidan-bidan Tersebut
        $pasienNifasIds = PasienNifasBidan::whereIn('bidan_id', $bidanIdsPuskesmas)->pluck('pasien_id'); // ID pasien nifas

        // 8. Card: Data Pasien Nifas (Total & Sudah KF1)
        $totalNifas = $pasienNifasIds->count(); // Total pasien nifas
        $sudahKf1 = Kf::whereIn('id_nifas', $pasienNifasIds) // Filter pasien nifas
                      ->where('kunjungan_nifas_ke', '>=', 1) // Minimal sudah KF1
                      ->distinct('id_nifas') // Hitung unik per pasien
                      ->count(); // Total sudah KF1

        // 9. Card: Pemantauan Nifas (Sehat/Dirujuk/Meninggal)
        // Ambil status kesimpulan pantauan dari tabel kf
        $pemantauanSehat = Kf::whereIn('id_nifas', $pasienNifasIds) // Filter pasien nifas
                             ->where('kesimpulan_pantauan', 'Sehat') // Status sehat
                             ->distinct('id_nifas') // Hitung unik per pasien
                             ->count(); // Total sehat
        
        $pemantauanDirujuk = Kf::whereIn('id_nifas', $pasienNifasIds) // Filter pasien nifas
                               ->where('kesimpulan_pantauan', 'Dirujuk') // Status dirujuk
                               ->distinct('id_nifas') // Hitung unik per pasien
                               ->count(); // Total dirujuk
        
        $pemantauanMeninggal = Kf::whereIn('id_nifas', $pasienNifasIds) // Filter pasien nifas
                                 ->where('kesimpulan_pantauan', 'Meninggal') // Status meninggal
                                 ->distinct('id_nifas') // Hitung unik per pasien
                                 ->count(); // Total meninggal

        // 10. Tabel: 5 Data Skrining Terbaru
        $pasienTerbaru = (clone $skriningsQuery) // Clone query skrining
                            ->with(['pasien.user']) // Eager load relasi pasien & user (hindari N+1 query)
                            ->latest() // Urutkan berdasarkan created_at terbaru
                            ->take(5) // Ambil 5 data teratas
                            ->get(); // Eksekusi query, return Collection
        
        // 11. Kirim Data ke View
        // compact(): ubah variable jadi array ['daerahAsal' => $daerahAsal, ...]
        return view('bidan.dashboard', compact(
            'daerahAsal',           // Data card daerah asal
            'resiko',               // Data card risiko
            'pasienHadir',          // Jumlah hadir
            'pasienTidakHadir',     // Jumlah tidak hadir
            'totalNifas',           // Total pasien nifas
            'sudahKf1',             // Sudah KF1
            'pemantauanSehat',      // Status sehat
            'pemantauanDirujuk',    // Status dirujuk
            'pemantauanMeninggal',  // Status meninggal
            'pasienTerbaru'         // 5 data terbaru untuk tabel
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