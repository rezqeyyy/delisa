<?php
// app/Http/Controllers/Puskesmas/RujukanController.php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk menangani proses rujukan pasien dari Puskesmas ke Rumah Sakit.
 * Meliputi pencarian rumah sakit, pengajuan rujukan, daftar rujukan, detail rujukan, dan pengecekan rujukan aktif.
 */
class RujukanController extends Controller
{
    /**
     * Endpoint AJAX untuk mencari rumah sakit berdasarkan input pencarian.
     * Digunakan untuk mengisi dropdown rumah sakit secara dinamis.
     *
     * @param Request $request Request yang berisi parameter pencarian 'q'.
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchRS(Request $request)
    {
        try {
            // Catatan: Mengambil parameter pencarian 'q' dari request.
            $search = $request->get('q', '');
            
            $query = DB::table('rumah_sakits');
            
            // Catatan: Jika ada input pencarian, tambahkan kondisi pencarian berdasarkan nama, kecamatan, atau kelurahan.
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('kecamatan', 'like', "%{$search}%")
                      ->orWhere('kelurahan', 'like', "%{$search}%");
                });
            }
            
            // Catatan: SESUAIKAN DENGAN STRUKTUR TABEL YANG ADA
            // Ambil kolom yang diperlukan untuk ditampilkan di dropdown.
            $rumahSakits = $query->select('id', 'nama', 'lokasi as alamat', 'kecamatan', 'kelurahan')->get();
            
            // Catatan: Kembalikan hasil dalam bentuk JSON.
            return response()->json($rumahSakits);
            
        } catch (\Exception $e) {
            // Catatan: Log error jika terjadi exception.
            Log::error('RS Search Error: ' . $e->getMessage());
            // Catatan: Kembalikan array kosong dengan status 500 (Internal Server Error).
            return response()->json([], 500);
        }
    }

    /**
     * Mengajukan rujukan baru dari detail skrining ke rumah sakit tertentu.
     *
     * @param Request $request Request yang berisi data rujukan (rs_id, catatan_rujukan).
     * @param int $skriningId ID dari skrining yang akan dirujuk.
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajukanRujukan(Request $request, $skriningId)
    {
        // Catatan: Validasi input dari form rujukan.
        $request->validate([
            'rs_id' => 'required|exists:rumah_sakits,id', // Harus ada dan sesuai dengan ID di tabel rumah_sakits.
            'catatan_rujukan' => 'nullable|string' // Opsional, harus berupa string.
        ]);

        // Catatan: Ambil data skrining untuk mendapatkan pasien_id.
        $skrining = DB::table('skrinings')
            ->where('id', $skriningId)
            ->first();

        // Catatan: Periksa apakah skrining ditemukan.
        if (!$skrining) {
            return response()->json([
                'success' => false,
                'message' => 'Data skrining tidak ditemukan'
            ], 404);
        }

        // Catatan: Cek apakah sudah ada rujukan aktif untuk skrining ini.
        $existingRujukan = DB::table('rujukan_rs')
            ->where('skrining_id', $skriningId)
            ->where('done_status', false) // Belum selesai
            ->first();

        // Catatan: Jika sudah ada rujukan aktif, kembalikan error.
        if ($existingRujukan) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien ini sudah memiliki rujukan aktif'
            ], 400);
        }

        // Catatan: Buat rujukan baru - SESUAI STRUCTURE EXISTING.
        $rujukanId = DB::table('rujukan_rs')->insertGetId([
            'pasien_id' => $skrining->pasien_id, // dari data skrining
            'rs_id' => $request->rs_id, // sesuai input form
            'skrining_id' => $skriningId,
            'done_status' => false, // default false = belum selesai
            'catatan_rujukan' => $request->input('catatan_rujukan') ?: null,
            'is_rujuk' => true, // true = dirujuk
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Catatan: TODO: Trigger notifikasi ke RS
        // $this->sendNotificationToRS($rujukanId);

        // Catatan: Kembalikan respons sukses.
        return response()->json([
            'success' => true,
            'message' => 'Rujukan berhasil diajukan',
            'rujukan_id' => $rujukanId
        ]);
    }

    /**
     * Menampilkan daftar semua rujukan yang diajukan oleh Puskesmas.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        try {
            // Catatan: Ambil daftar rujukan beserta informasi pasien, user, skrining, dan rumah sakit.
            $rujukans = DB::table('rujukan_rs')
                ->join('skrinings', 'rujukan_rs.skrining_id', '=', 'skrinings.id')
                ->join('pasiens', 'rujukan_rs.pasien_id', '=', 'pasiens.id')
                ->join('users', 'pasiens.user_id', '=', 'users.id')
                ->join('rumah_sakits', 'rujukan_rs.rs_id', '=', 'rumah_sakits.id')
                ->where('rujukan_rs.is_rujuk', true) // Hanya rujukan yang benar-benar diajukan
                ->select(
                    'rujukan_rs.*',
                    'users.name as nama_pasien', // ✅ dari users.name
                    'pasiens.nik',
                    'rumah_sakits.nama as nama_rs',
                    'skrinings.kesimpulan',
                    'skrinings.created_at as tanggal_skrining'
                )
                ->orderBy('rujukan_rs.created_at', 'desc') // Urutkan dari yang terbaru
                ->get();

            // Catatan: Kirim data rujukan ke view untuk ditampilkan.
            return view('puskesmas.rujukan.index', compact('rujukans'));
            
        } catch (\Exception $e) {
            // Catatan: Log error jika terjadi exception.
            Log::error('Rujukan Index Error: ' . $e->getMessage());
            // Catatan: Kembali ke halaman sebelumnya dengan pesan error.
            return back()->with('error', 'Gagal memuat data rujukan');
        }
    }

    /**
     * Menampilkan detail dari satu rujukan berdasarkan ID.
     *
     * @param int $id ID dari rujukan.
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        try {
            // Catatan: Ambil detail rujukan beserta informasi lengkap pasien, user, skrining, dan rumah sakit.
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

            // Catatan: Jika rujukan tidak ditemukan, tampilkan error 404.
            if (!$rujukan) {
                abort(404, 'Data rujukan tidak ditemukan');
            }

            // Catatan: Kirim detail rujukan ke view untuk ditampilkan.
            return view('puskesmas.rujukan.show', compact('rujukan'));
            
        } catch (\Exception $e) {
            // Catatan: Log error jika terjadi exception.
            Log::error('Rujukan Show Error: ' . $e->getMessage());
            // Catatan: Tampilkan error 500.
            abort(500, 'Gagal memuat detail rujukan');
        }
    }

    /**
     * Memeriksa apakah skrining tertentu sudah memiliki rujukan aktif.
     * Digunakan untuk mencegah pengajuan rujukan ganda.
     *
     * @param int $skriningId ID dari skrining.
     * @return bool
     */
    public function checkExistingRujukan($skriningId)
    {
        // Catatan: Cek apakah ada rujukan untuk skrining ini yang statusnya aktif (belum selesai dan benar-benar dirujuk).
        $hasReferral = DB::table('rujukan_rs')
            ->where('skrining_id', $skriningId)
            ->where('done_status', false) // Belum selesai
            ->where('is_rujuk', true) // Benar-benar dirujuk
            ->exists();

        return $hasReferral;
    }
}