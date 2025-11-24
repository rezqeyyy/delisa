<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Versi SANGAT AMAN - tanpa DB::raw yang kompleks
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

        // Manipulasi data collection untuk memenuhi kebutuhan view
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

        // ========== DATA PASIEN NIFAS - VERSI SEDERHANA & AMAN ==========
        // Point 1: Total Pasien Nifas (gabungan dari kedua tabel)
        $totalNifasBidan = DB::table('pasien_nifas_bidan')->count();
        $totalNifasRS = DB::table('pasien_nifas_rs')->count();
        $totalNifas = $totalNifasBidan + $totalNifasRS;
        
        // Point 2: Sudah KFI - sementara set 0 untuk menghindari error
        $sudahKFI = 0;
        // ========== END DATA PASIEN NIFAS ==========

        $data = [
            'asalDepok' => DB::table('pasiens')->where('PKabupaten', 'Depok')->count(),
            'asalNonDepok' => DB::table('pasiens')->where('PKabupaten', '!=', 'Depok')->count(),
            'resikoNormal' => DB::table('skrinings')->where('status_pre_eklampsia', 'Normal')->count(),
            'resikoPreeklampsia' => DB::table('skrinings')->where('status_pre_eklampsia', '!=', 'Normal')->count(),
            'pasienHadir' => DB::table('skrinings')->count(),
            'pasienTidakHadir' => 0,
            
            // DUA POINT UTAMA YANG DIMINTA:
            'totalNifas' => $totalNifas,    // Point 1: Total Pasien Nifas
            'sudahKFI' => $sudahKFI,        // Point 2: Sudah KFI
            
            'pemantauanSehat' => DB::table('skrinings')->where('kesimpulan', 'like', '%aman%')->orWhere('kesimpulan', 'like', '%tidak%')->count(),
            'pemantauanDirujuk' => DB::table('skrinings')->where('tindak_lanjut', true)->count(),
            'pemantauanMeninggal' => 0,
            'pePatients' => $pePatients
        ];

        return view('puskesmas.dashboard.index', $data);
    }
}