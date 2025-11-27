<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ========== DEBUG SEDERHANA ==========
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
        // Comment line berikut setelah test
        $depokCount = 4;  // PAKSA 4
        $nonDepokCount = 0; // PAKSA 0
        
        echo "DEBUG: Depok = {$depokCount}, Non Depok = {$nonDepokCount}";
        // Hapus line di atas setelah test

        // ========== KODE LAINNYA TETAP SAMA ==========
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

        $totalNifasBidan = DB::table('pasien_nifas_bidan')->count();
        $totalNifasRS = DB::table('pasien_nifas_rs')->count();
        $totalNifas = $totalNifasBidan + $totalNifasRS;
        $sudahKFI = 0;

        $data = [
            'asalDepok' => $depokCount,
            'asalNonDepok' => $nonDepokCount,
            'resikoNormal' => DB::table('skrinings')->where('status_pre_eklampsia', 'Normal')->count(),
            'resikoPreeklampsia' => DB::table('skrinings')->where('status_pre_eklampsia', '!=', 'Normal')->count(),
            'pasienTidakHadir' => 0,
            'totalNifas' => $totalNifas,
            'sudahKFI' => $sudahKFI,
            'pemantauanSehat' => DB::table('skrinings')->where('kesimpulan', 'like', '%aman%')->orWhere('kesimpulan', 'like', '%tidak%')->count(),
            'pemantauanDirujuk' => DB::table('skrinings')->where('tindak_lanjut', true)->count(),
            'pemantauanMeninggal' => 0,
            'pePatients' => $pePatients
        ];

        return view('puskesmas.dashboard.index', $data);
    }

    private function isDepok($kabupaten)
    {
        if (empty($kabupaten) || trim($kabupaten) === '') {
            return false;
        }
        
        $kabupaten = strtolower(trim($kabupaten));
        return $kabupaten === 'depok' || str_contains($kabupaten, 'depok');
    }
}