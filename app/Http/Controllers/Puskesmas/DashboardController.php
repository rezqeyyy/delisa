<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama.
     */
    public function index()
    {
        // ========== HITUNG PASIEN DEPOK/NON DEPOK ==========
        $totalPasien = DB::table('pasiens')->count();
        
        $depokCount = DB::table('pasiens')
            ->where(function($query) {
                $query->whereRaw('LOWER("PKabupaten") LIKE ?', ['%depok%'])
                      ->orWhere('PKabupaten', 'ilike', '%depok%');
            })
            ->count();
        
        $nonDepokCount = $totalPasien - $depokCount;
        
        // ========== DATA PASIEN PRE-EKLAMPSIA ==========
        $userId = Auth::id();
        $ps = DB::table('puskesmas')->select('id','kecamatan')->where('user_id', $userId)->first();
        $kecamatan = optional($ps)->kecamatan;

        $pePatients = DB::table('skrinings')
            ->join('pasiens', 'skrinings.pasien_id', '=', 'pasiens.id')
            ->join('users', 'users.id', '=', 'pasiens.user_id')
            ->leftJoin('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id')
            ->whereNotNull('skrinings.status_pre_eklampsia')
            ->when($kecamatan, function ($q) use ($kecamatan) {
                $q->where(function ($w) use ($kecamatan) {
                    $w->where('pasiens.PKecamatan', $kecamatan)
                      ->orWhere('puskesmas.kecamatan', $kecamatan);
                });
            })
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
            ->paginate(5);

        $pePatients->getCollection()->transform(function ($item) {
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

        // ========== DATA PASIEN NIFAS & KFI ==========
        $totalNifas = DB::table('pasien_nifas_rs')->count();
        
        $pasienHadir = DB::table('pasien_nifas_rs')
            ->whereNotNull('kf1_tanggal')
            ->count();
        
        $pasienTidakHadir = DB::table('pasien_nifas_rs')
            ->whereNull('kf1_tanggal')
            ->count();
        
        $sudahKFI = DB::table('pasien_nifas_rs')
            ->whereNotNull('kf1_tanggal')
            ->whereNotNull('kf2_tanggal')
            ->whereNotNull('kf3_tanggal')
            ->whereNotNull('kf4_tanggal')
            ->count();

        // ========== RESIKO PRE-EKLAMPSIA ==========
        $resikoNormal = DB::table('skrinings')
            ->where('status_pre_eklampsia', 'Normal')
            ->count();

        $resikoPreeklampsia = DB::table('skrinings')
            ->where('status_pre_eklampsia', 'Risiko Tinggi')
            ->count();

        // Fallback jika tidak ada data dengan status
        if ($resikoNormal == 0 && $resikoPreeklampsia == 0) {
            $resikoNormal = DB::table('skrinings')
                ->where(function($query) {
                    $query->where('kesimpulan', 'ILIKE', '%tidak%')
                          ->orWhere('kesimpulan', 'ILIKE', '%normal%')
                          ->orWhere('kesimpulan', 'ILIKE', '%aman%');
                })
                ->count();

            $resikoPreeklampsia = DB::table('skrinings')
                ->where(function($query) {
                    $query->where('kesimpulan', 'ILIKE', '%berisiko%')
                          ->orWhere('kesimpulan', 'ILIKE', '%risiko%')
                          ->orWhere('kesimpulan', 'ILIKE', '%tinggi%');
                })
                ->count();
        }

        // ========== PEMANTAUAN ==========
        $pemantauanSehat = $resikoNormal;
        
        // Total Dirujuk: dari skrinings.tindak_lanjut = true
        $pemantauanDirujuk = DB::table('skrinings')
            ->where('tindak_lanjut', true)
            ->count();
        
        // Meninggal: Cek apakah ada di tabel lain atau sementara 0
        // Karena tidak ada kolom meninggal di rujukan_nifas, kita cek tabel lain
        $pemantauanMeninggal = 0;
        
        // OPTIONAL: Jika mau cek dari tabel kondisi_kesehatans atau riwayat_penyakit_nifas
        // Uncomment jika ada data meninggal di tabel tersebut
        /*
        $pemantauanMeninggal = DB::table('kondisi_kesehatans')
            ->where('status', 'meninggal')
            ->orWhere('keterangan', 'ILIKE', '%meninggal%')
            ->orWhere('kondisi', 'ILIKE', '%meninggal%')
            ->count();
        */

        // ========== DATA UNTUK VIEW ==========
        $data = [
            'asalDepok' => $depokCount,
            'asalNonDepok' => $nonDepokCount,
            'resikoNormal' => $resikoNormal,
            'resikoPreeklampsia' => $resikoPreeklampsia,
            'pasienHadir' => $pasienHadir,
            'pasienTidakHadir' => $pasienTidakHadir,
            'totalNifas' => $totalNifas,
            'sudahKFI' => $sudahKFI,
            'pemantauanSehat' => $pemantauanSehat,
            'pemantauanDirujuk' => $pemantauanDirujuk,
            'pemantauanMeninggal' => $pemantauanMeninggal,
            'pePatients' => $pePatients
        ];

        // Debug log
        Log::info("Dashboard Data: ", [
            'totalNifas' => $totalNifas,
            'pasienHadir' => $pasienHadir,
            'pasienTidakHadir' => $pasienTidakHadir,
            'sudahKFI' => $sudahKFI,
            'resikoNormal' => $resikoNormal,
            'resikoPreeklampsia' => $resikoPreeklampsia,
            'pemantauanSehat' => $pemantauanSehat,
            'pemantauanDirujuk' => $pemantauanDirujuk,
            'pemantauanMeninggal' => $pemantauanMeninggal,
        ]);

        return view('puskesmas.dashboard.index', $data);
    }
}