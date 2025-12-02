<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama.
     */
    public function index()
    {
        // ========== HITUNG PASIEN DEPOK/NON DEPOK ==========
        $totalPasien = DB::table('pasiens')->count();
        
        // Untuk PostgreSQL, gunakan nama kolom yang tepat
        // Jika PKabupaten tidak ada, coba cek nama kolom sebenarnya
        $depokCount = DB::table('pasiens')
            ->where(function($query) {
                $query->whereRaw('LOWER("PKabupaten") LIKE ?', ['%depok%'])
                      ->orWhere('PKabupaten', 'ilike', '%depok%');
            })
            ->count();
        
        $nonDepokCount = $totalPasien - $depokCount;
        
        // Log untuk debugging
        Log::info("Dashboard Debug: Total Pasien = {$totalPasien}, Depok = {$depokCount}, Non Depok = {$nonDepokCount}");
        
        // ========== DEBUG: CEK KOLOM YANG ADA ==========
        // Uncomment baris ini untuk melihat kolom yang ada di tabel pasiens
        // $columns = DB::getSchemaBuilder()->getColumnListing('pasiens');
        // dd($columns); // Akan menampilkan semua kolom di tabel pasiens
        
        // ========== DATA PASIEN PRE-EKLAMPSIA ==========
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

        // ========== DATA PASIEN NIFAS ==========
        $totalNifasBidan = DB::table('pasien_nifas_bidan')->count();
        $totalNifasRS = DB::table('pasien_nifas_rs')->count();
        $totalNifas = $totalNifasBidan + $totalNifasRS;
        $sudahKFI = 0;

        // ========== STATISTIK LAINNYA ==========
        $resikoNormal = DB::table('skrinings')->where('status_pre_eklampsia', 'Normal')->count();
        $resikoPreeklampsia = DB::table('skrinings')->where('status_pre_eklampsia', '!=', 'Normal')->count();

        $data = [
            'asalDepok' => $depokCount,
            'asalNonDepok' => $nonDepokCount,
            'resikoNormal' => $resikoNormal,
            'resikoPreeklampsia' => $resikoPreeklampsia,
            'pasienHadir' => 0,
            'pasienTidakHadir' => 0,
            'totalNifas' => $totalNifas,
            'sudahKFI' => $sudahKFI,
            'pemantauanSehat' => DB::table('skrinings')
                ->where(function($query) {
                    $query->where('kesimpulan', 'like', '%aman%')
                          ->orWhere('kesimpulan', 'like', '%tidak%')
                          ->orWhere('kesimpulan', 'like', '%normal%');
                })
                ->count(),
            'pemantauanDirujuk' => DB::table('skrinings')->where('tindak_lanjut', true)->count(),
            'pemantauanMeninggal' => 0,
            'pePatients' => $pePatients
        ];

        return view('puskesmas.dashboard.index', $data);
    }
}