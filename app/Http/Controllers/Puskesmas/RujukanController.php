<?php
// app/Http/Controllers/Puskesmas/RujukanController.php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RujukanController extends Controller
{
    // AJAX Search RS untuk dropdown
    public function searchRS(Request $request)
    {
        try {
            $search = $request->get('q', '');
            
            $query = DB::table('rumah_sakits');
            
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('kecamatan', 'like', "%{$search}%")
                      ->orWhere('kelurahan', 'like', "%{$search}%");
                });
            }
            
            // SESUAIKAN DENGAN STRUKTUR TABEL YANG ADA
            $rumahSakits = $query->select('id', 'nama', 'lokasi as alamat', 'kecamatan', 'kelurahan')->get();
            
            return response()->json($rumahSakits);
            
        } catch (\Exception $e) {
            Log::error('RS Search Error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    // Submit rujukan dari detail skrining
    public function ajukanRujukan(Request $request, $skriningId)
    {
        $request->validate([
            'rs_id' => 'required|exists:rumah_sakits,id',
            'catatan_rujukan' => 'required|string|min:10'
        ]);

        // Get data skrining untuk dapatkan pasien_id
        $skrining = DB::table('skrinings')
            ->where('id', $skriningId)
            ->first();

        if (!$skrining) {
            return response()->json([
                'success' => false,
                'message' => 'Data skrining tidak ditemukan'
            ], 404);
        }

        // Cek apakah sudah ada rujukan aktif
        $existingRujukan = DB::table('rujukan_rs')
            ->where('skrining_id', $skriningId)
            ->where('done_status', false) // Belum selesai
            ->first();

        if ($existingRujukan) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien ini sudah memiliki rujukan aktif'
            ], 400);
        }

        // Create rujukan - SESUAI STRUCTURE EXISTING
        $rujukanId = DB::table('rujukan_rs')->insertGetId([
            'pasien_id' => $skrining->pasien_id, // dari data skrining
            'rs_id' => $request->rs_id, // sesuai input form
            'skrining_id' => $skriningId,
            'done_status' => false, // default false = belum selesai
            'catatan_rujukan' => $request->catatan_rujukan,
            'is_rujuk' => true, // true = dirujuk
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // TODO: Trigger notifikasi ke RS
        // $this->sendNotificationToRS($rujukanId);

        return response()->json([
            'success' => true,
            'message' => 'Rujukan berhasil diajukan',
            'rujukan_id' => $rujukanId
        ]);
    }

    // List rujukan dari puskesmas
    public function index()
    {
        try {
            $rujukans = DB::table('rujukan_rs')
                ->join('skrinings', 'rujukan_rs.skrining_id', '=', 'skrinings.id')
                ->join('pasiens', 'rujukan_rs.pasien_id', '=', 'pasiens.id')
                ->join('users', 'pasiens.user_id', '=', 'users.id')
                ->join('rumah_sakits', 'rujukan_rs.rs_id', '=', 'rumah_sakits.id')
                ->where('rujukan_rs.is_rujuk', true)
                ->select(
                    'rujukan_rs.*',
                    'users.name as nama_pasien', // ✅ dari users.name
                    'pasiens.nik',
                    'rumah_sakits.nama as nama_rs',
                    'skrinings.kesimpulan',
                    'skrinings.created_at as tanggal_skrining'
                )
                ->orderBy('rujukan_rs.created_at', 'desc')
                ->get();

            return view('puskesmas.rujukan.index', compact('rujukans'));
            
        } catch (\Exception $e) {
            Log::error('Rujukan Index Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat data rujukan');
        }
    }

    // Detail rujukan
    public function show($id)
    {
        try {
            $rujukan = DB::table('rujukan_rs')
                ->join('skrinings', 'rujukan_rs.skrining_id', '=', 'skrinings.id')
                ->join('pasiens', 'rujukan_rs.pasien_id', '=', 'pasiens.id')
                ->join('users', 'pasiens.user_id', '=', 'users.id')
                ->join('rumah_sakits', 'rujukan_rs.rs_id', '=', 'rumah_sakits.id')
                ->where('rujukan_rs.id', $id)
                ->select(
                    'rujukan_rs.*',
                    'users.name as nama_pasien',        // ✅ dari users.name
                    'pasiens.nik',
                    'pasiens.tanggal_lahir',
                    'users.address as alamat',          // ✅ dari users.address
                    'users.phone as no_telepon',        // ✅ dari users.phone
                    'rumah_sakits.nama as nama_rs',
                    'rumah_sakits.lokasi as alamat_rs', // ✅ dari rumah_sakits.lokasi
                    'rumah_sakits.telepon as telepon_rs',
                    'skrinings.kesimpulan',
                    'skrinings.hasil_akhir'
                )
                ->first();

            if (!$rujukan) {
                abort(404, 'Data rujukan tidak ditemukan');
            }

            return view('puskesmas.rujukan.show', compact('rujukan'));
            
        } catch (\Exception $e) {
            Log::error('Rujukan Show Error: ' . $e->getMessage());
            abort(500, 'Gagal memuat detail rujukan');
        }
    }

    // Update status untuk cek apakah sudah ada rujukan aktif
    public function checkExistingRujukan($skriningId)
    {
        $hasReferral = DB::table('rujukan_rs')
            ->where('skrining_id', $skriningId)
            ->where('done_status', false) // Belum selesai
            ->where('is_rujuk', true) // Benar-benar dirujuk
            ->exists();

        return $hasReferral;
    }
}