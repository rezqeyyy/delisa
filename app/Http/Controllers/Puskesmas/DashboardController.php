<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk menangani halaman dashboard Puskesmas.
 * Menyediakan data statistik dan daftar pasien untuk ditampilkan di halaman utama.
 */
class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama beserta data statistik dan pasien.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // ========== DEBUG SEDERHANA ==========
        // Catatan: Mengambil semua data pasien hanya untuk menghitung asal Depok/non-Depok.
        // Jika tabel 'pasiens' besar, ini bisa menjadi tidak efisien. Pertimbangkan query agregat di DB.
        $allPasien = DB::table('pasiens')->select('PKabupaten')->get();
        
        $depokCount = 0;
        $nonDepokCount = 0;
        
        foreach ($allPasien as $pasien) {
            if ($this->isDepok($pasien->PKabupaten)) {
                $depokCount++;
            } else {
                $nonDepokCount++;
            }
        }

        // ========== PAKSA HASIL UNTUK TEST ==========
        // Catatan: Bagian ini digunakan untuk keperluan testing. Harus dihapus atau dinonaktifkan setelah testing.
        // Comment line berikut setelah test
        $depokCount = 4;  // PAKSA 4
        $nonDepokCount = 0; // PAKSA 0
        
        echo "DEBUG: Depok = {$depokCount}, Non Depok = {$nonDepokCount}";
        // Hapus line di atas setelah test

        // ========== KODE LAINNYA TETAP SAMA ==========
        // Catatan: Mengambil data skrining beserta informasi pasien dan user.
        // Digunakan untuk menampilkan daftar pasien dengan status pre-eklampsia.
        $pePatients = DB::table('skrinings')
            ->join('pasiens', 'skrinings.pasien_id', '=', 'pasiens.id')
            ->join('users', 'users.id', '=', 'pasiens.user_id')
            ->select(
                'skrinings.id',
                'skrinings.status_pre_eklampsia',
                'skrinings.kesimpulan',
                'skrinings.tindak_lanjut',
                'skrinings.created_at',
                'pasiens.nik',
                'pasiens.tanggal_lahir',
                'pasiens.PKecamatan',
                'users.name as nama_pasien',
                'users.address as alamat_domisili',
                'users.phone as phone'
            )
            ->orderBy('skrinings.created_at', 'desc')
            ->get();

        // Catatan: Menyusun ulang struktur data pasien untuk tampilan.
        // Menggabungkan alamat domisili dan kecamatan jika alamat kosong.
        $pePatients = $pePatients->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'nama' => $item->nama_pasien,
                'nik' => $item->nik,
                'tanggal' => $item->tanggal_lahir,
                'alamat' => $item->alamat_domisili ?? $item->PKecamatan,
                'telp' => $item->phone ?? '-',
                'kesimpulan' => $item->kesimpulan,
                'hasil_akhir' => $item->kesimpulan,
                'rekomendasi' => '-',
                'status_pre_eklampsia' => $item->status_pre_eklampsia,
                'tindak_lanjut' => $item->tindak_lanjut
            ];
        });

        // Catatan: Menghitung total pasien nifas dari dua sumber: bidan dan rumah sakit.
        $totalNifasBidan = DB::table('pasien_nifas_bidan')->count();
        $totalNifasRS = DB::table('pasien_nifas_rs')->count();
        $totalNifas = $totalNifasBidan + $totalNifasRS;
        $sudahKFI = 0; // Catatan: Nilai ini sepertinya statis, mungkin akan diimplementasikan nanti.

        $data = [
            'asalDepok' => $depokCount,
            'asalNonDepok' => $nonDepokCount,
            'resikoNormal' => DB::table('skrinings')->where('status_pre_eklampsia', 'Normal')->count(),
            'resikoPreeklampsia' => DB::table('skrinings')->where('status_pre_eklampsia', '!=', 'Normal')->count(), // Catatan: Menghitung semua status selain 'Normal'.
            'pasienTidakHadir' => 0, // Catatan: Nilai statis, mungkin akan diimplementasikan nanti.
            'totalNifas' => $totalNifas,
            'sudahKFI' => $sudahKFI,
            'pemantauanSehat' => DB::table('skrinings')->where('kesimpulan', 'like', '%aman%')->orWhere('kesimpulan', 'like', '%tidak%')->count(), // Catatan: Mencari kesimpulan yang mengandung kata 'aman' atau 'tidak'.
            'pemantauanDirujuk' => DB::table('skrinings')->where('tindak_lanjut', true)->count(), // Catatan: Asumsi tindak_lanjut adalah boolean.
            'pemantauanMeninggal' => 0, // Catatan: Nilai statis, mungkin akan diimplementasikan nanti.
            'pePatients' => $pePatients
        ];

        return view('puskesmas.dashboard.index', $data);
    }

    /**
     * Memeriksa apakah nama kabupaten berasal dari Depok.
     * Digunakan untuk menghitung jumlah pasien dari Depok.
     *
     * @param string|null $kabupaten Nama kabupaten dari data pasien.
     * @return bool True jika nama kabupaten mengandung kata 'depok', false jika tidak.
     */
    private function isDepok($kabupaten)
    {
        // Catatan: Memeriksa apakah input kosong.
        if (empty($kabupaten) || trim($kabupaten) === '') {
            return false;
        }
        
        // Catatan: Mengubah menjadi huruf kecil untuk pencocokan yang tidak sensitif terhadap huruf besar/kecil.
        $kabupaten = strtolower(trim($kabupaten));
        return $kabupaten === 'depok' || str_contains($kabupaten, 'depok');
    }
}