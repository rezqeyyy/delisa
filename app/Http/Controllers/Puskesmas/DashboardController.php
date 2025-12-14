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
        // ========== AMBIL KECAMATAN DARI RELASI USER ==========
        $user = Auth::user();
        Log::info("Dashboard diakses oleh User ID: {$user->id}, Name: {$user->name}");

        $puskesmas = $user->puskesmas; // Gunakan relasi Eloquent

        if (!$puskesmas) {
            $bidan = $user->bidan ?? null;
            if ($bidan && $bidan->puskesmas_id) {
                $puskesmasId = $bidan->puskesmas_id;
                $kecamatan = DB::table('puskesmas')->where('id', $puskesmasId)->value('kecamatan');
                Log::info("Fallback Bidan → Puskesmas ID: {$puskesmasId}, Kecamatan: " . ($kecamatan ?? 'NULL'));
            } else {
                Log::error("User {$user->id} tidak memiliki relasi puskesmas");
                $puskesmasId = null;
                $kecamatan = null;
            }
        } else {
            $puskesmasId = $puskesmas->id;
            $kecamatan = $puskesmas->kecamatan;
            Log::info("Kecamatan dari relasi puskesmas: {$kecamatan}");
        }
        
        Log::info("Kecamatan dari puskesmas: " . ($kecamatan ?? 'NULL'));
        
        // ========== DEBUG: Cek apakah ada data ==========
        $debugTotalSkrining = DB::table('skrinings')->count();
        $debugTotalPasien = DB::table('pasiens')->count();
        
        // ========== HITUNG PASIEN DEPOK/NON DEPOK ==========
        // PERUBAHAN 1: Hanya hitung pasien yang melakukan skrining di puskesmas ini
        $queryPasien = DB::table('pasiens')
            ->join('skrinings', 'pasiens.id', '=', 'skrinings.pasien_id')
            ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id');
        
        if ($kecamatan) {
            $queryPasien->where('puskesmas.kecamatan', $kecamatan);
        }
        
        $totalPasien = $queryPasien->distinct('pasiens.id')->count();
        
        Log::info("Total pasien setelah filter: {$totalPasien}");
        
        $depokCount = DB::table('pasiens')
            ->join('skrinings', 'pasiens.id', '=', 'skrinings.pasien_id')
            ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id')
            ->where(function($query) {
                $query->whereRaw('LOWER("PKabupaten") LIKE ?', ['%depok%'])
                      ->orWhere('PKabupaten', 'ilike', '%depok%');
            })
            ->when($kecamatan, function ($q) use ($kecamatan) {
                $q->where('puskesmas.kecamatan', $kecamatan);
            })
            ->distinct('pasiens.id')
            ->count();
        
        $nonDepokCount = $totalPasien - $depokCount;
        
        Log::info("Depok: {$depokCount}, Non Depok: {$nonDepokCount}");

        // ========== DATA PASIEN PRE-EKLAMPSIA ==========
        $peQuery = DB::table('skrinings')
            ->join('pasiens', 'skrinings.pasien_id', '=', 'pasiens.id')
            ->join('users', 'users.id', '=', 'pasiens.user_id')
            ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id')
            ->whereNotNull('skrinings.status_pre_eklampsia');
        
        // Debug: tampilkan query sebelum filter
        $debugBeforeFilter = $peQuery->count();
        Log::info("Data pre-eklampsia SEBELUM filter kecamatan: {$debugBeforeFilter}");
        
        // PERUBAHAN 2: HANYA filter berdasarkan puskesmas yang dipilih
        if ($kecamatan) {
            $peQuery->where('puskesmas.kecamatan', $kecamatan);
            
            // Debug: tampilkan query setelah filter
            $debugAfterFilter = $peQuery->count();
            Log::info("Data pre-eklampsia SETELAH filter kecamatan '{$kecamatan}': {$debugAfterFilter}");
        }
        
        $pePatients = $peQuery
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
                'users.phone as phone',
                'puskesmas.kecamatan as puskesmas_kecamatan' // Tambah untuk debug
            )
            ->orderBy('skrinings.created_at', 'desc')
            ->paginate(5);
        
        Log::info("Total data pre-eklampsia untuk pagination: " . $pePatients->total());

        // Transform data
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
                'tindak_lanjut' => $item->tindak_lanjut,
                'debug_PKecamatan' => $item->PKecamatan,
                'debug_puskesmas_kecamatan' => $item->puskesmas_kecamatan
            ];
        });

        // ========== DATA PASIEN NIFAS & KFI ==========
        // PERUBAHAN 3: Untuk data nifas, cari dari skrining terakhir pasien
        $totalNifas = DB::table('pasien_nifas_rs as pnr')
            ->where('pnr.puskesmas_id', $puskesmasId)
            ->count();
        
        $pasienHadir = DB::table('pasien_nifas_rs as pnr')
            ->where('pnr.puskesmas_id', $puskesmasId)
            ->leftJoin('kf_kunjungans as kk', 'kk.pasien_nifas_id', '=', 'pnr.id')
            ->whereNotNull('kk.id')
            ->distinct('pnr.id')
            ->count('pnr.id');
        
        $pasienTidakHadir = DB::table('pasien_nifas_rs as pnr')
            ->where('pnr.puskesmas_id', $puskesmasId)
            ->leftJoin('kf_kunjungans as kk', 'kk.pasien_nifas_id', '=', 'pnr.id')
            ->whereNull('kk.id')
            ->distinct('pnr.id')
            ->count('pnr.id');
        
        $sudahKFI = DB::table('pasien_nifas_rs as pnr')
            ->where('pnr.puskesmas_id', $puskesmasId)
            ->whereNotNull('kf1_tanggal')
            ->whereNotNull('kf2_tanggal')
            ->whereNotNull('kf3_tanggal')
            ->whereNotNull('kf4_tanggal')
            ->count();
        
        Log::info("Data Nifas - Total: {$totalNifas}, Hadir: {$pasienHadir}, Tidak Hadir: {$pasienTidakHadir}, Sudah KFI: {$sudahKFI}");

        // ========== RESIKO PRE-EKLAMPSIA ==========
        $resikoNormal = DB::table('skrinings')
            ->join('pasiens', 'skrinings.pasien_id', '=', 'pasiens.id')
            ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id') // GANTI leftJoin MENJADI join
            ->where('status_pre_eklampsia', 'Normal')
            ->when($kecamatan, function ($q) use ($kecamatan) {
                $q->where('puskesmas.kecamatan', $kecamatan);
            })
            ->count();

        $resikoPreeklampsia = DB::table('skrinings')
            ->join('pasiens', 'skrinings.pasien_id', '=', 'pasiens.id')
            ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id')
            ->where('status_pre_eklampsia', 'Risiko Tinggi')
            ->when($kecamatan, function ($q) use ($kecamatan) {
                // PERUBAHAN 5: HANYA filter berdasarkan puskesmas
                $q->where('puskesmas.kecamatan', $kecamatan);
            })
            ->count();

        // Fallback jika tidak ada data dengan status
        if ($resikoNormal == 0 && $resikoPreeklampsia == 0) {
            Log::info("Menggunakan fallback query untuk resiko pre-eklampsia");
            
            $resikoNormal = DB::table('skrinings')
                ->join('pasiens', 'skrinings.pasien_id', '=', 'pasiens.id')
                ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id')
                ->where(function($query) {
                    $query->where('kesimpulan', 'ILIKE', '%tidak%')
                          ->orWhere('kesimpulan', 'ILIKE', '%normal%')
                          ->orWhere('kesimpulan', 'ILIKE', '%aman%');
                })
                ->when($kecamatan, function ($q) use ($kecamatan) {
                    // PERUBAHAN 6: HANYA filter berdasarkan puskesmas
                    $q->where('puskesmas.kecamatan', $kecamatan);
                })
                ->count();

            $resikoPreeklampsia = DB::table('skrinings')
                ->join('pasiens', 'skrinings.pasien_id', '=', 'pasiens.id')
                ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id')
                ->where(function($query) {
                    $query->where('kesimpulan', 'ILIKE', '%berisiko%')
                          ->orWhere('kesimpulan', 'ILIKE', '%risiko%')
                          ->orWhere('kesimpulan', 'ILIKE', '%tinggi%');
                })
                ->when($kecamatan, function ($q) use ($kecamatan) {
                    // PERUBAHAN 7: HANYA filter berdasarkan puskesmas
                    $q->where('puskesmas.kecamatan', $kecamatan);
                })
                ->count();
        }
        
        Log::info("Resiko - Normal: {$resikoNormal}, Preeklampsia: {$resikoPreeklampsia}");

        // ========== PEMANTAUAN ==========
        $pemantauanSehat = $resikoNormal;
        
        $pemantauanDirujuk = DB::table('skrinings')
            ->join('pasiens', 'skrinings.pasien_id', '=', 'pasiens.id')
            ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id')
            ->where('tindak_lanjut', true)
            ->when($kecamatan, function ($q) use ($kecamatan) {
                // PERUBAHAN 8: HANYA filter berdasarkan puskesmas
                $q->where('puskesmas.kecamatan', $kecamatan);
            })
            ->count();
        
        $pemantauanMeninggal = 0;
        
        Log::info("Pemantauan - Sehat: {$pemantauanSehat}, Dirujuk: {$pemantauanDirujuk}, Meninggal: {$pemantauanMeninggal}");

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
            'pePatients' => $pePatients,
            'kecamatanFilter' => $kecamatan,
            'debugInfo' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'kecamatan' => $kecamatan,
                'total_pasien' => $totalPasien,
                'total_skrining' => $debugTotalSkrining
            ]
        ];

        return view('puskesmas.dashboard.index', $data);
    }
}