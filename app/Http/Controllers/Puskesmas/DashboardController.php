<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use App\Models\Skrining; // Import model Skrining
use App\Models\Pasien;   // Import model Pasien jika diperlukan secara terpisah
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil semua skrining (Anda mungkin ingin filter berdasarkan puskesmas tertentu nanti)
        // Gunakan with('pasien') untuk mengambil data pasien terkait secara efisien (eager loading)
        $skriningQuery = Skrining::with('pasien');
        $allSkrining = $skriningQuery->get();

        // 1. Daerah Asal Pasien (Contoh: berdasarkan PKabupaten)
        $depokCount = $allSkrining->where('pasien.PKabupaten', 'Kota Depok')->count();
        $nonDepokCount = $allSkrining->count() - $depokCount; // Hitung sisanya sebagai Non Depok

        // 2. Data Pasien Nifas (Asumsikan kolom status_nifas ada di tabel skrinings)
        // Jika kolom ini belum ada, Anda perlu menambahkannya dan mengisi nilainya saat skrining dibuat
        // $totalNifas = $allSkrining->where('status_nifas', true)->count(); // Ganti 'status_nifas' dengan nama kolom yang benar
        // $sudahKFI = $allSkrining->where('status_nifas', true)->where('sudah_kfi', true)->count(); // Ganti 'sudah_kfi' dengan nama kolom yang benar
        // Sementara, jika kolom belum ada, gunakan 0
        $totalNifas = 0;
        $sudahKFI = 0;

        // 3. Resiko Eklampsia (Berdasarkan kolom kesimpulan di tabel skrinings)
        // SESUAIKAN DENGAN NILAI YANG ADA DI DATABASE
        $normalEklampsia = $allSkrining->where('kesimpulan', 'Normal')->count(); // Ganti nilai sesuai data Anda
        $waspadaiEklampsia = $allSkrining->where('kesimpulan', 'Waspada')->count(); // <- GANTI INI
        $beresikoEklampsia = $allSkrining->where('kesimpulan', 'Beresiko')->count(); // <- GANTI INI JUGA, ATAU BUAT LAIN JIKA ADA

        // 4. Pasien Hadir (Asumsikan kolom hadir ada di tabel skrinings)
        // Jika kolom ini belum ada, Anda perlu menambahkannya
        // $hadir = $allSkrining->where('hadir', true)->count(); // Ganti 'hadir' dengan nama kolom yang benar
        // $tidakHadir = $allSkrining->where('hadir', false)->count();
        // Sementara, jika kolom belum ada, gunakan 0
        $hadir = 0;
        $tidakHadir = 0;

        // 5. Pemantauan (Asumsikan kolom pemantauan_status ada di tabel skrinings)
        // Jika kolom ini belum ada, Anda perlu menambahkannya
        // $sehat = $allSkrining->where('pemantauan_status', 'Sehat')->count(); // Ganti 'pemantauan_status' dengan nama kolom yang benar
        // $dirujuk = $allSkrining->where('pemantauan_status', 'Dirujuk')->count();
        // $meninggal = $allSkrining->where('pemantauan_status', 'Meninggal')->count();
        // Sementara, jika kolom belum ada, gunakan 0
        $sehat = 0;
        $dirujuk = 0;
        $meninggal = 0;

        // 6. Tabel Data Pasien Pre Eklampsia (ambil 5 skrining dengan kesimpulan 'Beresiko')
        // SESUAIKAN DENGAN NILAI YANG ADA DI DATABASE
        $preEklampsiaData = $skriningQuery->where('kesimpulan', 'Waspada')->take(5)->get(); // <- GANTI INI JUGA

        // Kirim semua data ke view
        return view('puskesmas.dashboard.index', compact(
            'depokCount',
            'nonDepokCount',
            'totalNifas',
            'sudahKFI',
            'normalEklampsia',
            'waspadaiEklampsia',
            'beresikoEklampsia',
            'hadir',
            'tidakHadir',
            'sehat',
            'dirujuk',
            'meninggal',
            'preEklampsiaData'
        ));
    }
}