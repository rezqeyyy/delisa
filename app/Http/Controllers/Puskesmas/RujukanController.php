<?php
namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RujukanController extends Controller
{
    /**
     * Cari rumah sakit
     */
    public function searchRS(Request $request)
    {
        try {
            $search = $request->get('q', '');
            $query = DB::table('rumah_sakits');
            
            if (!empty($search)) {
                $query->where('nama', 'like', "%{$search}%");
            }
            
            return response()->json($query->select('id', 'nama')->get());
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Ajukan rujukan
     */
    public function ajukanRujukan(Request $request, $skriningId)
    {
        try {
            $request->validate([
                'rs_id' => 'required|exists:rumah_sakits,id',
                'catatan_rujukan' => 'nullable|string'
            ]);

            $skrining = DB::table('skrinings')->where('id', $skriningId)->first();
            
            if (!$skrining) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data skrining tidak ditemukan'
                ], 404);
            }

            // Cek rujukan aktif
            $existing = DB::table('rujukan_rs')
                ->where('skrining_id', $skriningId)
                ->where('done_status', 0)
                ->exists();
                
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien sudah memiliki rujukan aktif'
                ], 400);
            }

            // Insert rujukan baru
            $rujukanId = DB::table('rujukan_rs')->insertGetId([
                'pasien_id' => $skrining->pasien_id,
                'rs_id' => $request->rs_id,
                'skrining_id' => $skriningId,
                'done_status' => 0,
                'catatan_rujukan' => $request->catatan_rujukan,
                'is_rujuk' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rujukan berhasil diajukan',
                'rujukan_id' => $rujukanId,
                'redirect_url' => route('puskesmas.rujukan.index')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tampilkan daftar rujukan - VERSI SIMPLE
     */
    public function index()
    {
        try {
            // AMBIL DATA DENGAN CARA SEDERHANA
            $rujukans = DB::table('rujukan_rs')
                ->where('is_rujuk', 1)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // JIKA ADA DATA, AMBIL INFORMASI TAMBAHAN
            if ($rujukans->isNotEmpty()) {
                foreach ($rujukans as $rujukan) {
                    // Ambil nama pasien
                    $pasien = DB::table('pasiens')
                        ->join('users', 'pasiens.user_id', '=', 'users.id')
                        ->where('pasiens.id', $rujukan->pasien_id)
                        ->select('users.name as nama_pasien', 'pasiens.nik')
                        ->first();
                    
                    $rujukan->nama_pasien = $pasien->nama_pasien ?? 'Tidak diketahui';
                    $rujukan->nik = $pasien->nik ?? '-';
                    
                    // Ambil nama rumah sakit
                    $rs = DB::table('rumah_sakits')
                        ->where('id', $rujukan->rs_id)
                        ->select('nama as nama_rs')
                        ->first();
                    
                    $rujukan->nama_rs = $rs->nama_rs ?? 'Tidak diketahui';
                }
            }

            return view('puskesmas.rujukan.index', compact('rujukans'));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data rujukan');
        }
    }

    /**
     * Tampilkan detail rujukan - VERSI SIMPLE & PASTI BEKERJA
     */
    public function show($id)
    {
        try {
            // 1. AMBIL DATA DASAR RUJUKAN
            $rujukan = DB::table('rujukan_rs')
                ->where('id', $id)
                ->first();
            
            if (!$rujukan) {
                return response()->view('errors.404', [], 404);
            }
            
            // 2. AMBIL DATA PASIEN
            $pasien = DB::table('pasiens')
                ->join('users', 'pasiens.user_id', '=', 'users.id')
                ->where('pasiens.id', $rujukan->pasien_id)
                ->select('users.name as nama_pasien', 'pasiens.nik', 'pasiens.tanggal_lahir', 'users.address as alamat', 'users.phone as no_telepon')
                ->first();
            
            if ($pasien) {
                $rujukan->nama_pasien = $pasien->nama_pasien;
                $rujukan->nik = $pasien->nik;
                $rujukan->tanggal_lahir = $pasien->tanggal_lahir;
                $rujukan->alamat = $pasien->alamat;
                $rujukan->no_telepon = $pasien->no_telepon;
            }
            
            // 3. AMBIL DATA RUMAH SAKIT
            $rumahSakit = DB::table('rumah_sakits')
                ->where('id', $rujukan->rs_id)
                ->select('nama as nama_rs', 'lokasi as alamat_rs', 'telepon as telepon_rs')
                ->first();
            
            if ($rumahSakit) {
                $rujukan->nama_rs = $rumahSakit->nama_rs;
                $rujukan->alamat_rs = $rumahSakit->alamat_rs;
                $rujukan->telepon_rs = $rumahSakit->telepon_rs;
            }
            
            // 4. AMBIL DATA SKRINING
            $skrining = DB::table('skrinings')
                ->where('id', $rujukan->skrining_id)
                ->select('kesimpulan', 'hasil_akhir')
                ->first();
            
            if ($skrining) {
                $rujukan->kesimpulan = $skrining->kesimpulan;
                $rujukan->hasil_akhir = $skrining->hasil_akhir;
            }
            
            return view('puskesmas.rujukan.show', compact('rujukan'));
            
        } catch (\Exception $e) {
            return response()->view('errors.500', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update status rujukan
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'done_status' => 'required|boolean'
            ]);
            
            $updated = DB::table('rujukan_rs')
                ->where('id', $id)
                ->update([
                    'done_status' => $request->done_status ? 1 : 0,
                    'updated_at' => now()
                ]);
            
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diperbarui',
                'status_text' => $request->done_status ? 'Selesai' : 'Menunggu Konfirmasi RS'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * Cek rujukan aktif
     */
    public function checkExistingRujukan($skriningId)
    {
        return DB::table('rujukan_rs')
            ->where('skrining_id', $skriningId)
            ->where('done_status', 0)
            ->where('is_rujuk', 1)
            ->exists();
    }
}