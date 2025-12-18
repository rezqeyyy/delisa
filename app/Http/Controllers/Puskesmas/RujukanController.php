<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

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

    private function resolvePuskesmasContext(?int $userId)
    {
        if (!$userId) return null;

        // 1) Normal: user pemilik row puskesmas (puskesmas.user_id = users.id)
        $ps = DB::table('puskesmas')
            ->select('id', 'kecamatan', 'is_mandiri')
            ->where('user_id', $userId)
            ->first();

        if ($ps) return $ps;

        // 2) Bidan (nebeng puskesmas): bidans.puskesmas_id -> puskesmas.id
        return DB::table('bidans')
            ->join('puskesmas', 'puskesmas.id', '=', 'bidans.puskesmas_id')
            ->where('bidans.user_id', $userId)
            ->select('puskesmas.id', 'puskesmas.kecamatan', 'puskesmas.is_mandiri')
            ->first();
    }

    private function applyWilayahFilterToSkriningJoin($query, $ps)
    {
        $kecamatan = trim((string) ($ps->kecamatan ?? ''));

        return $query->where(function ($w) use ($ps, $kecamatan) {
            // 1) Skrining yang dilakukan di puskesmas ini
            $w->where('skrinings.puskesmas_id', $ps->id);

            // 2) Skrining yang dilakukan di faskes manapun yang kecamatannya sama
            // (mencakup klinik mandiri karena tetap row di tabel puskesmas)
            if ($kecamatan !== '') {
                $w->orWhereRaw('LOWER("puskesmas"."kecamatan") = LOWER(?)', [$kecamatan]);
            }
        });
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
    public function index(Request $request)
    {
        try {
            // ===============================
            // 0. Ambil parameter search
            // ===============================
            $search = trim($request->get('search'));
            $userId = optional(Auth::user())->id;
            $ps = $this->resolvePuskesmasContext($userId);
            abort_unless($ps, 404);


            // ===============================
            // 1. BASE QUERY
            // - Rujukan aktif
            // - Skrining berisiko
            // - Sudah JOIN pasien, user, RS (untuk search)
            // ===============================
            $baseQuery = DB::table('rujukan_rs')
                ->join('skrinings', 'rujukan_rs.skrining_id', '=', 'skrinings.id')
                ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id') // ✅ penting
                ->join('pasiens', 'rujukan_rs.pasien_id', '=', 'pasiens.id')
                ->join('users', 'pasiens.user_id', '=', 'users.id')
                ->join('rumah_sakits', 'rujukan_rs.rs_id', '=', 'rumah_sakits.id')
                ->where('rujukan_rs.is_rujuk', 1)
                ->where(function ($q) {
                    $q->where('skrinings.jumlah_resiko_tinggi', '>=', 1)
                        ->orWhere('skrinings.jumlah_resiko_sedang', '>=', 2);
                });

            // ✅ kunci wilayah: hanya rujukan dari skrining yang “masuk” list skrining puskesmas ini
            $this->applyWilayahFilterToSkriningJoin($baseQuery, $ps);

            // SEARCH tetap
            $baseQuery->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->whereRaw('LOWER(users.name) LIKE ?', ['%' . strtolower($search) . '%'])
                        ->orWhere('pasiens.nik', 'ILIKE', "%{$search}%")
                        ->orWhereRaw('LOWER(rumah_sakits.nama) LIKE ?', ['%' . strtolower($search) . '%']);
                });
            });


            // ===============================
            // 3. Ambil ID rujukan TERBARU per pasien
            // ===============================
            $latestRujukanIds = $baseQuery
                ->select(DB::raw('MAX(rujukan_rs.id) as id'))
                ->groupBy('rujukan_rs.pasien_id')
                ->pluck('id');

            // ===============================
            // 4. Ambil data final rujukan
            // ===============================
            if ($latestRujukanIds->isEmpty()) {
                $rujukans = collect();
            } else {
                $rujukans = DB::table('rujukan_rs')
                    ->join('skrinings', 'rujukan_rs.skrining_id', '=', 'skrinings.id')
                    ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id') // ✅ penting
                    ->join('pasiens', 'rujukan_rs.pasien_id', '=', 'pasiens.id')
                    ->join('users', 'pasiens.user_id', '=', 'users.id')
                    ->join('rumah_sakits', 'rujukan_rs.rs_id', '=', 'rumah_sakits.id')
                    ->whereIn('rujukan_rs.id', $latestRujukanIds);

                // ✅ kunci wilayah lagi di query final
                $this->applyWilayahFilterToSkriningJoin($rujukans, $ps);

                $rujukans = $rujukans
                    ->orderBy('rujukan_rs.created_at', 'desc')
                    ->select(
                        'rujukan_rs.*',
                        'users.name as nama_pasien',
                        'pasiens.nik',
                        'rumah_sakits.nama as nama_rs'
                    )
                    ->paginate(10);

                $rujukans->appends($request->except('page'));
            }

            return view('puskesmas.rujukan.index', compact('rujukans'));
        } catch (\Exception $e) {
            Log::error('ERROR Puskesmas.RujukanController@index', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal memuat data rujukan');
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

            $userId = optional(Auth::user())->id;
            $ps = $this->resolvePuskesmasContext($userId);
            abort_unless($ps, 404);

            // Ambil kecamatan faskes tempat skrining rujukan ini
            $kecPuskesmas = mb_strtolower(trim((string) ($ps->kecamatan ?? '')));

            $row = DB::table('skrinings')
                ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id')
                ->where('skrinings.id', $rujukan->skrining_id)
                ->select('skrinings.puskesmas_id', 'puskesmas.kecamatan')
                ->first();

            abort_unless($row, 404);

            $kecFaskes = mb_strtolower(trim((string) ($row->kecamatan ?? '')));

            $allowed = (
                ((int)$row->puskesmas_id === (int)$ps->id)
                || ($kecFaskes !== '' && $kecPuskesmas !== '' && $kecFaskes === $kecPuskesmas)
            );

            abort_unless($allowed, 403);


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
                $rujukan->catatan       = $riwayat->catatan;
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

    /**
     * Download PDF detail rujukan pasien
     */
    public function downloadPdf($id)
    {
        try {
            // Reuse logic dari show(): ambil data rujukan + enrich
            $rujukan = DB::table('rujukan_rs')
                ->where('id', $id)
                ->first();

            if (!$rujukan) {
                return back()->with('error', 'Data rujukan tidak ditemukan');
            }

            $userId = optional(Auth::user())->id;
            $ps = $this->resolvePuskesmasContext($userId);
            abort_unless($ps, 404);

            $row = DB::table('skrinings')
                ->join('puskesmas', 'puskesmas.id', '=', 'skrinings.puskesmas_id')
                ->where('skrinings.id', $rujukan->skrining_id)
                ->select('skrinings.puskesmas_id', 'puskesmas.kecamatan')
                ->first();

            abort_unless($row, 404);

            $kecPuskesmas = mb_strtolower(trim((string) ($ps->kecamatan ?? '')));
            $kecFaskes    = mb_strtolower(trim((string) ($row->kecamatan ?? '')));
            $allowed = (
                ((int)$row->puskesmas_id === (int)$ps->id)
                || ($kecFaskes !== '' && $kecPuskesmas !== '' && $kecFaskes === $kecPuskesmas)
            );
            abort_unless($allowed, 403);

            // Pasien
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

            if ($pasien) {
                $rujukan->nama_pasien   = $pasien->nama_pasien;
                $rujukan->nik           = $pasien->nik;
                $rujukan->tanggal_lahir = $pasien->tanggal_lahir;
                $rujukan->alamat        = $pasien->alamat;
                $rujukan->no_telepon    = $pasien->no_telepon;
            }

            // Rumah sakit
            $rs = DB::table('rumah_sakits')
                ->where('id', $rujukan->rs_id)
                ->select('nama as nama_rs', 'lokasi as alamat_rs', 'telepon as telepon_rs')
                ->first();
            if ($rs) {
                $rujukan->nama_rs    = $rs->nama_rs;
                $rujukan->alamat_rs  = $rs->alamat_rs;
                $rujukan->telepon_rs = $rs->telepon_rs;
            }

            // Skrining ringkas
            $skrining = DB::table('skrinings')
                ->where('id', $rujukan->skrining_id)
                ->select('kesimpulan')
                ->first();
            if ($skrining) {
                $rujukan->kesimpulan = $skrining->kesimpulan;
            }

            // Riwayat rujukan dari RS (balasan)
            $riwayat = DB::table('riwayat_rujukans')
                ->where('rujukan_id', $rujukan->id)
                ->orderByDesc('tanggal_datang')
                ->first();
            if ($riwayat) {
                $rujukan->anjuran_kontrol      = $riwayat->anjuran_kontrol;
                $rujukan->kunjungan_berikutnya = $riwayat->kunjungan_berikutnya;
                $rujukan->catatan              = $riwayat->catatan;
            }

            // Generate PDF
            $pdf = Pdf::loadView('puskesmas.rujukan.pdf', [
                'rujukan' => $rujukan,
            ]);
            $pdf->setPaper('A4', 'portrait');

            $patientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $rujukan->nama_pasien ?? 'Pasien');
            $fileName = 'Rujukan_' . $patientName . '_' . date('Y-m-d') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Throwable $e) {
            Log::error('Puskesmas.RujukanController@downloadPdf ERROR', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Gagal mengunduh PDF: ' . $e->getMessage());
        }
    }
}
