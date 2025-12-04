<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RujukanController extends Controller
{
    /**
     * Cari rumah sakit
     */
    public function searchRS(Request $request)
    {
        $q = trim($request->get('q', ''));

        // 🔍 DEBUG LOG
        Log::info('Puskesmas.RujukanController@searchRS dipanggil', [
            'query'   => $q,
            'user_id' => Auth::id(),
        ]);

        try {
            $query = DB::table('rumah_sakits');

            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    // Pakai ILIKE biar case-insensitive di PostgreSQL
                    $w->where('nama', 'ILIKE', "%{$q}%")
                        ->orWhere('kecamatan', 'ILIKE', "%{$q}%")
                        ->orWhere('kelurahan', 'ILIKE', "%{$q}%")
                        ->orWhere('lokasi', 'ILIKE', "%{$q}%");
                });
            }

            $data = $query
                ->select(
                    'id',
                    'nama',
                    DB::raw('lokasi as alamat'),
                    'kecamatan',
                    'kelurahan'
                )
                ->orderBy('nama')
                ->get();

            // 🔍 DEBUG LOG
            Log::info('Puskesmas.RujukanController@searchRS hasil', [
                'count' => $data->count(),
            ]);

            return response()->json($data);
        } catch (\Throwable $e) {
            // 🔥 DEBUG ERROR
            Log::error('Puskesmas.RujukanController@searchRS ERROR', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Gagal memuat daftar rumah sakit',
            ], 500);
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

            // ===============================
            // 1) CEK RISIKO SKRINING
            // ===============================
            $resikoSedang = (int)($skrining->jumlah_resiko_sedang ?? 0);
            $resikoTinggi = (int)($skrining->jumlah_resiko_tinggi ?? 0);
            $isBerisiko   = ($resikoTinggi >= 1 || $resikoSedang >= 2);

            // Debug log untuk memantau logic risiko
            Log::info('Puskesmas.RujukanController@ajukanRujukan', [
                'skrining_id'       => $skriningId,
                'jumlah_resiko_sedang' => $resikoSedang,
                'jumlah_resiko_tinggi' => $resikoTinggi,
                'is_berisiko'          => $isBerisiko,
            ]);

            if (!$isBerisiko) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien ini tidak berisiko preeklampsia sehingga tidak dapat diajukan rujukan.'
                ], 400);
            }

            // ===============================
            // 2) CEK RUJUKAN AKTIF
            // ===============================
            $existing = DB::table('rujukan_rs')
                ->where('skrining_id', $skriningId)
                ->where('done_status', 0)   // hanya yang statusnya masih "menunggu"
                ->where('is_rujuk', 1)      // rujukan aktif
                ->exists();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien sudah memiliki rujukan aktif yang belum selesai.'
                ], 400);
            }

            // ===============================
            // 3) INSERT RUJUKAN BARU
            // ===============================
            $rujukanId = DB::table('rujukan_rs')->insertGetId([
                'pasien_id'        => $skrining->pasien_id,
                'rs_id'            => $request->rs_id,
                'skrining_id'      => $skriningId,
                'done_status'      => 0, // status "Menunggu konfirmasi RS"
                'catatan_rujukan'  => $request->catatan_rujukan,
                'is_rujuk'         => 1,
                'created_at'       => now(),
                'updated_at'       => now()
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Rujukan berhasil diajukan',
                'rujukan_id'   => $rujukanId,
                'redirect_url' => route('puskesmas.rujukan.index')
            ]);
        } catch (\Exception $e) {
            Log::error('ERROR ajukanRujukan: ' . $e->getMessage(), [
                'skrining_id' => $skriningId,
                'trace'       => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Tampilkan daftar rujukan
     *
     * Sekarang: hanya menampilkan SATU baris per pasien (unique per pasien_id),
     * dan data rujukan yang dipakai adalah rujukan TERBARU pasien tersebut.
     */
    public function index()
    {
        try {
            // -------------------------------------------------
            // 1. BASE QUERY: ambil semua rujukan BERISIKO
            //    - Hanya rujukan yang memang diajukan (is_rujuk = 1)
            //    - Hanya skrining yang memenuhi kriteria berisiko:
            //      jumlah_resiko_tinggi >= 1 ATAU jumlah_resiko_sedang >= 2
            // -------------------------------------------------
            $baseQuery = DB::table('rujukan_rs')
                ->join('skrinings', 'rujukan_rs.skrining_id', '=', 'skrinings.id')
                ->where('rujukan_rs.is_rujuk', 1)
                ->where(function ($q) {
                    $q->where('skrinings.jumlah_resiko_tinggi', '>=', 1)
                        ->orWhere('skrinings.jumlah_resiko_sedang', '>=', 2);
                });

            // -------------------------------------------------
            // 2. DAPATKAN ID RUJUKAN TERBARU PER PASIEN
            //    - Group by pasien_id
            //    - Ambil MAX(rujukan_rs.id) -> rujukan terbaru pasien tsb
            // -------------------------------------------------
            $latestRujukanIds = $baseQuery
                ->select(DB::raw('MAX(rujukan_rs.id) as id'))
                ->groupBy('rujukan_rs.pasien_id')
                ->pluck('id');   // hasil: koleksi [id1, id2, id3, ...]

            // Kalau tidak ada rujukan sama sekali
            if ($latestRujukanIds->isEmpty()) {
                $rujukans = collect();
            } else {
                // -------------------------------------------------
                // 3. AMBIL DETAIL RUJUKAN BERDASARKAN ID TERBARU TADI
                //    - Hanya rujukan dengan id yang masuk list MAX(id)
                //    - Urutkan dari yang paling baru dibuat
                // -------------------------------------------------
                $rujukans = DB::table('rujukan_rs')
                    ->whereIn('id', $latestRujukanIds)
                    ->orderBy('created_at', 'desc')
                    ->get();

                // -------------------------------------------------
                // 4. LENGKAPI DENGAN NAMA PASIEN, NIK, NAMA RS
                // -------------------------------------------------
                foreach ($rujukans as $rujukan) {
                    // Ambil nama & NIK pasien
                    $pasien = DB::table('pasiens')
                        ->join('users', 'pasiens.user_id', '=', 'users.id')
                        ->where('pasiens.id', $rujukan->pasien_id)
                        ->select('users.name as nama_pasien', 'pasiens.nik')
                        ->first();

                    $rujukan->nama_pasien = $pasien->nama_pasien ?? 'Tidak diketahui';
                    $rujukan->nik         = $pasien->nik ?? '-';

                    // Ambil nama RS tujuan
                    $rs = DB::table('rumah_sakits')
                        ->where('id', $rujukan->rs_id)
                        ->select('nama as nama_rs')
                        ->first();

                    $rujukan->nama_rs = $rs->nama_rs ?? 'Tidak diketahui';
                }
            }

            return view('puskesmas.rujukan.index', compact('rujukans'));
        } catch (\Exception $e) {
            Log::error('ERROR Puskesmas.RujukanController@index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal memuat data rujukan: ' . $e->getMessage());
        }
    }


    /**
     * Tampilkan detail rujukan - DEBUG VERSION
     */
    public function show($id)
    {
        try {
            Log::info('=== DEBUG SHOW RUJUKAN START ===');
            Log::info('ID yang dicari: ' . $id);

            // 1. AMBIL DATA DASAR RUJUKAN
            $rujukan = DB::table('rujukan_rs')
                ->where('id', $id)
                ->first();

            Log::info('Rujukan ditemukan: ' . ($rujukan ? 'YA' : 'TIDAK'));

            if (!$rujukan) {
                Log::error('Rujukan tidak ditemukan!');
                return back()->with('error', 'Data rujukan dengan ID ' . $id . ' tidak ditemukan');
            }

            Log::info('Data rujukan: ' . json_encode($rujukan));

            // 2. AMBIL DATA PASIEN
            Log::info('Mencari pasien ID: ' . $rujukan->pasien_id);

            $pasien = DB::table('pasiens')
                ->join('users', 'pasiens.user_id', '=', 'users.id')
                ->where('pasiens.id', $rujukan->pasien_id)
                ->select(
                    'users.name as nama_pasien',
                    'pasiens.nik',
                    'pasiens.tanggal_lahir',
                    'users.address as alamat',
                    'users.phone as no_telepon'
                )
                ->first();

            Log::info('Pasien ditemukan: ' . ($pasien ? 'YA' : 'TIDAK'));

            if ($pasien) {
                $rujukan->nama_pasien   = $pasien->nama_pasien;
                $rujukan->nik           = $pasien->nik;
                $rujukan->tanggal_lahir = $pasien->tanggal_lahir;
                $rujukan->alamat        = $pasien->alamat;
                $rujukan->no_telepon    = $pasien->no_telepon;
            }

            // 3. AMBIL DATA RUMAH SAKIT
            Log::info('Mencari RS ID: ' . $rujukan->rs_id);

            $rumahSakit = DB::table('rumah_sakits')
                ->where('id', $rujukan->rs_id)
                ->select('nama as nama_rs', 'lokasi as alamat_rs', 'telepon as telepon_rs')
                ->first();

            Log::info('RS ditemukan: ' . ($rumahSakit ? 'YA' : 'TIDAK'));

            if ($rumahSakit) {
                $rujukan->nama_rs   = $rumahSakit->nama_rs;
                $rujukan->alamat_rs = $rumahSakit->alamat_rs;
                $rujukan->telepon_rs = $rumahSakit->telepon_rs;
            }

            // 4. AMBIL DATA SKRINING
            Log::info('Mencari skrining ID: ' . $rujukan->skrining_id);

            $skrining = DB::table('skrinings')
                ->where('id', $rujukan->skrining_id)
                ->select('kesimpulan')
                ->first();

            Log::info('Skrining ditemukan: ' . ($skrining ? 'YA' : 'TIDAK'));

            if ($skrining) {
                $rujukan->kesimpulan = $skrining->kesimpulan;
            }

            // 5. AMBIL DATA RIWAYAT RUJUKAN DARI RS
            Log::info('Mencari riwayat_rujukans untuk rujukan_id: ' . $rujukan->id);

            $riwayat = DB::table('riwayat_rujukans')
                ->where('rujukan_id', $rujukan->id)
                ->orderByDesc('tanggal_datang')
                ->first();

            Log::info('Riwayat rujukan ditemukan: ' . ($riwayat ? 'YA' : 'TIDAK'));

            if ($riwayat) {
                $rujukan->anjuran_kontrol      = $riwayat->anjuran_kontrol;
                $rujukan->kunjungan_berikutnya = $riwayat->kunjungan_berikutnya;
                // Kalau nanti mau ditampilkan juga:
                // $rujukan->tindakan_rs      = $riwayat->tindakan;
                // $rujukan->catatan_rs       = $riwayat->catatan;
                // $rujukan->tekanan_darah_rs = $riwayat->tekanan_darah;
            }

            Log::info('Akan render view dengan data lengkap');
            Log::info('=== DEBUG SHOW RUJUKAN END ===');

            return view('puskesmas.rujukan.show', compact('rujukan'));
        } catch (\Exception $e) {
            Log::error('ERROR di show(): ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()->with('error', 'ERROR: ' . $e->getMessage());
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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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
