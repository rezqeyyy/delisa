<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk menangani data pasien nifas dari rumah sakit.
 * Menyediakan daftar pasien nifas dan statistik terkait KFI (Kunjungan Fisik Ibu).
 */
class PasienNifasController extends Controller
{
    /**
     * Menampilkan daftar pasien nifas dari rumah sakit beserta statistiknya.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Catatan: Mengambil data pasien nifas dari rumah sakit (pasien_nifas_rs) beserta informasi pasien, user, dan rumah sakit.
        // Digunakan DB query builder untuk menggabungkan tabel dan memilih kolom yang diperlukan.
        $pasienNifas = DB::table('pasien_nifas_rs')
            ->join('pasiens', 'pasien_nifas_rs.pasien_id', '=', 'pasiens.id')
            ->join('users', 'pasiens.user_id', '=', 'users.id') // Join ke users untuk mendapatkan nama pasien.
            ->join('rumah_sakits', 'pasien_nifas_rs.rs_id', '=', 'rumah_sakits.id')
            ->select(
                'pasien_nifas_rs.id',
                'pasien_nifas_rs.pasien_id',
                'pasien_nifas_rs.tanggal_mulai_nifas as tanggal', // Alias untuk tanggal_mulai_nifas
                'pasien_nifas_rs.created_at',
                'pasiens.nik',
                'users.name as nama_pasien', // Ambil nama dari users
                'pasiens.tanggal_lahir',
                'pasiens.PKecamatan as alamat', // Alias untuk PKecamatan
                'pasiens.PKabupaten',
                'rumah_sakits.nama as nama_rs'
            )
            ->orderBy('pasien_nifas_rs.created_at', 'desc') // Urutkan dari yang terbaru
            ->paginate(10); // Paginasi untuk menampilkan 10 data per halaman

        // Catatan: Menghitung total jumlah pasien nifas dari rumah sakit.
        $totalPasienNifas = DB::table('pasien_nifas_rs')->count();

        // Catatan: Variabel ini sepertinya statis untuk saat ini. Mungkin akan diimplementasikan untuk menghitung pasien yang sudah KFI.
        $sudahKFI = 0;

        // Catatan: Jumlah pasien yang belum KFI adalah total dikurangi yang sudah KFI.
        $belumKFI = $totalPasienNifas;

        // Catatan: Mengirim data ke view menggunakan compact untuk memudahkan pembacaan.
        return view('puskesmas.pasien-nifas.index', compact(
            'pasienNifas',
            'totalPasienNifas',
            'sudahKFI',
            'belumKFI'
        ));
    }
}