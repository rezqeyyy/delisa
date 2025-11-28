<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\Skrining;
use App\Models\RujukanRs;
use App\Models\ResepObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| RS SKRINING CONTROLLER
|--------------------------------------------------------------------------
| Fungsi: Mengelola data skrining pasien rujukan untuk Rumah Sakit
| Fitur: List rujukan, detail pasien, form pemeriksaan, resep obat
|--------------------------------------------------------------------------
*/

class SkriningController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | METHOD: index()
    |--------------------------------------------------------------------------
    | Fungsi: Menampilkan daftar pasien rujukan yang sudah diterima RS
    | Return: View 'rs.skrining.index' dengan data rujukan paginated
    | 
    | ALUR PROSES:
    | 1. Ambil ID Rumah Sakit yang login
    | 2. Query rujukan yang done_status=true & is_rujuk=true
    | 3. Transform data untuk format tampilan (nama, NIK, kesimpulan)
    | 4. Kirim ke view dengan pagination
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        // 1. Ambil ID Rumah Sakit yang Login
        // Auth::user()->rumahSakit: akses relasi rumahSakit dari user
        // ->id: ambil ID rumah sakit, null jika tidak ada
        $rsId = Auth::user()->rumahSakit->id ?? null;

        // 2. Query Rujukan yang Sudah Diterima RS
        // with(['skrining.pasien.user']): eager load relasi nested
        $skrinings = RujukanRs::with(['skrining.pasien.user'])
            ->where('rs_id', $rsId)          // Filter per RS yang login
            ->where('done_status', true)     // Sudah diterima/diproses RS
            ->where('is_rujuk', true)        // Sudah dirujuk dari puskesmas
            ->orderByDesc('created_at')      // Urutkan terbaru dulu
            ->paginate(10);                  // 10 data per halaman

        // 3. Transform Data untuk Tampilan View
        // Loop setiap rujukan untuk set property tambahan
        $skrinings->getCollection()->transform(function ($rujukan) {
            // Ambil data skrining dari rujukan
            $skr = $rujukan->skrining;
            
            // Ambil data pasien dari skrining (optional: cegah error jika null)
            $pas = optional($skr)->pasien;
            
            // Ambil data user dari pasien
            $usr = optional($pas)->user;

            // Tentukan Level Risiko Pasien
            // Ambil kesimpulan dan ubah ke lowercase
            $raw = strtolower(trim($skr->kesimpulan ?? $skr->status_pre_eklampsia ?? ''));
            
            // Cek apakah risiko tinggi
            $isHigh = ($skr->jumlah_resiko_tinggi ?? 0) > 0  // Ada risiko tinggi dari hitungan
                || in_array($raw, ['beresiko','berisiko','risiko tinggi','tinggi']); // Atau dari kesimpulan
            
            // Cek apakah risiko sedang
            $isMed  = ($skr->jumlah_resiko_sedang ?? 0) > 0   // Ada risiko sedang dari hitungan
                || in_array($raw, ['waspada','menengah','sedang','risiko sedang']); // Atau dari kesimpulan

            // Set Property Tambahan ke Object Rujukan
            $rujukan->nik        = $pas->nik ?? '-';                        // NIK pasien
            $rujukan->nama       = $usr->name ?? 'Nama Tidak Tersedia';    // Nama pasien
            $rujukan->tanggal    = optional($skr->created_at)->format('d/m/Y'); // Tanggal skrining
            $rujukan->alamat     = $pas->PKecamatan ?? $pas->PWilayah ?? '-';   // Alamat (Kecamatan/Kelurahan)
            $rujukan->telp       = $usr->phone ?? $pas->no_telepon ?? '-';      // No telepon
            
            // Kesimpulan risiko dengan format standar
            $rujukan->kesimpulan = $isHigh ? 'Beresiko' :           // Risiko tinggi -> Beresiko
                                  ($isMed ? 'Waspada' :             // Risiko sedang -> Waspada
                                  'Tidak Berisiko');                 // Normal -> Tidak Berisiko

            // URL untuk aksi
            $rujukan->detail_url  = route('rs.skrining.show', $skr->id);  // Link ke detail
            $rujukan->process_url = route('rs.skrining.edit', $skr->id);  // Link ke form pemeriksaan

            return $rujukan; // Return object yang sudah ditransform
        });

        // 4. Kirim ke View
        return view('rs.skrining.index', compact('skrinings'));
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: show()
    |--------------------------------------------------------------------------
    | Fungsi: Menampilkan detail lengkap pasien rujukan & resep obat
    | Parameter: $id (ID skrining)
    | Return: View 'rs.skrining.show' dengan data skrining & rujukan
    | 
    | ALUR PROSES:
    | 1. Ambil data skrining dengan semua relasi
    | 2. Cari rujukan RS untuk skrining ini
    | 3. Ambil resep obat (prioritas rujukan_rs_id, fallback riwayat_rujukan_id)
    | 4. Kirim ke view
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        // 1. Ambil Data Skrining dengan Relasi
        // findOrFail($id): cari by ID, 404 jika tidak ada
        $skrining = Skrining::with([
            'pasien.user',           // Data pasien & user (nama, NIK, telp)
            'kondisiKesehatan',      // Data kesehatan (IMT, tekanan darah)
            'riwayatKehamilanGpa',   // Data GPA (Gravida, Para, Abortus)
            'puskesmas'              // Data puskesmas asal rujukan
        ])->findOrFail($id);

        // 2. Ambil ID Rumah Sakit yang Login
        $rsId = Auth::user()->rumahSakit->id ?? null;

        // 3. Cari Rujukan RS untuk Skrining Ini
        // where(): filter berdasarkan skrining_id & rs_id
        // first(): ambil 1 data pertama, null jika tidak ada
        $rujukan = RujukanRs::where('skrining_id', $skrining->id)
            ->where('rs_id', $rsId)
            ->first();

        // 4. Inisialisasi Collection Resep Obat
        $resepObats = collect(); // Collection kosong

        // 5. Ambil Resep Obat Jika Rujukan Ada
        if ($rujukan) {
            // PRIORITAS 1: Ambil resep berdasarkan rujukan_rs_id
            // Kolom rujukan_rs_id adalah FK utama untuk resep obat
            $resepObats = ResepObat::where('rujukan_rs_id', $rujukan->id)->get();

            // FALLBACK: Jika tidak ada resep di rujukan_rs_id
            if ($resepObats->isEmpty()) {
                // Cari riwayat rujukan (tabel legacy/alternatif)
                $riwayat = DB::table('riwayat_rujukans')
                    ->where('rujukan_id', $rujukan->id)
                    ->first();

                // Jika ada riwayat, coba ambil resep dari riwayat_rujukan_id
                if ($riwayat) {
                    $resepObats = ResepObat::where('riwayat_rujukan_id', $riwayat->id)->get();
                }
            }
        }

        // 6. Kirim ke View
        return view('rs.skrining.show', compact('skrining', 'rujukan', 'resepObats'));
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: edit()
    |--------------------------------------------------------------------------
    | Fungsi: Menampilkan form pemeriksaan pasien rujukan
    | Parameter: $id (ID skrining)
    | Return: View 'rs.skrining.edit' dengan form data
    | 
    | ALUR PROSES:
    | 1. Ambil data skrining dengan relasi
    | 2. Buat/ambil rujukan RS (firstOrCreate)
    | 3. Ambil riwayat rujukan jika ada
    | 4. Ambil resep obat existing
    | 5. Kirim semua data ke view
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        // 1. Ambil Data Skrining dengan Relasi
        $skrining = Skrining::with([
            'pasien.user',
            'kondisiKesehatan',
            'riwayatKehamilanGpa',
            'puskesmas'
        ])->findOrFail($id);

        // 2. Ambil ID Rumah Sakit
        $rsId = Auth::user()->rumahSakit->id ?? null;

        // 3. Buat/Ambil Rujukan RS
        // firstOrCreate(): ambil jika ada, buat baru jika tidak ada
        // Parameter 1: kondisi pencarian
        // Parameter 2: data default jika buat baru
        $rujukan = RujukanRs::firstOrCreate(
            [
                'skrining_id' => $skrining->id,  // FK ke skrining
                'pasien_id'   => $skrining->pasien_id, // FK ke pasien
                'rs_id'       => $rsId,          // FK ke rumah sakit
            ],
            [
                'done_status' => false,          // Belum selesai (default)
                'is_rujuk'    => false,          // Belum dirujuk (default)
            ]
        );

        // 4. Ambil Riwayat Rujukan (Jika Ada)
        // Query manual ke tabel riwayat_rujukans
        $riwayatRujukan = DB::table('riwayat_rujukans')
            ->where('rujukan_id', $rujukan->id)
            ->first();

        // 5. Ambil Resep Obat Existing
        // PRIORITAS: resep berdasarkan rujukan_rs_id (kolom utama)
        $resepObats = ResepObat::where('rujukan_rs_id', $rujukan->id)->get();

        // FALLBACK: kalau kosong dan ada riwayat_rujukan
        if ($resepObats->isEmpty() && $riwayatRujukan) {
            // Coba ambil via riwayat_rujukan_id (kolom alternatif)
            $resepObats = ResepObat::where('riwayat_rujukan_id', $riwayatRujukan->id)->get();
        }

        // 6. Kirim ke View
        return view('rs.skrining.edit', compact(
            'skrining',         // Data skrining lengkap
            'rujukan',          // Data rujukan RS
            'resepObats',       // Collection resep obat
            'riwayatRujukan'    // Data riwayat rujukan
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: update()
    |--------------------------------------------------------------------------
    | Fungsi: Menyimpan hasil pemeriksaan pasien & resep obat
    | Parameter: $request (data form), $id (ID skrining)
    | Return: Redirect ke show dengan pesan sukses
    | 
    | ALUR PROSES:
    | 1. Validasi input form
    | 2. Mulai database transaction
    | 3. Update/buat rujukan RS
    | 4. Update/buat riwayat rujukan
    | 5. Hapus resep lama & simpan resep baru
    | 6. Commit transaction & redirect
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        // 1. Ambil Data Skrining
        $skrining = Skrining::findOrFail($id);
        
        // 2. Ambil ID Rumah Sakit
        $rsId = Auth::user()->rumahSakit->id ?? null;

        // 3. Validasi Input Form
        $validated = $request->validate([
            // Data Rujukan
            'catatan_rujukan'       => 'nullable|string',  // Catatan dari RS (opsional)
            'riwayat_tekanan_darah' => 'nullable|string',  // Tekanan darah saat periksa
            'tindakan'              => 'nullable|string',  // Tindakan medis yang dilakukan

            // Data Resep Obat (Array)
            'resep_obat'      => 'nullable|array',      // Array nama obat
            'resep_obat.*'    => 'nullable|string',     // Setiap item nama obat (string)
            'dosis'           => 'nullable|array',      // Array dosis obat
            'dosis.*'         => 'nullable|string',     // Setiap item dosis (string)
            'penggunaan'      => 'nullable|array',      // Array aturan penggunaan
            'penggunaan.*'    => 'nullable|string',     // Setiap item penggunaan (string)
        ]);

        // 4. Simpan Data dalam Transaction
        // Transaction: semua query sukses semua, atau rollback jika ada error
        DB::transaction(function () use ($skrining, $rsId, $validated) {
            
            // 4a. Update/Buat Rujukan RS
            // updateOrCreate(): update jika ada, buat baru jika tidak ada
            $rujukan = RujukanRs::updateOrCreate(
                [
                    // Kondisi pencarian
                    'skrining_id' => $skrining->id,
                    'pasien_id'   => $skrining->pasien_id,
                    'rs_id'       => $rsId
                ],
                [
                    // Data yang di-update/insert
                    'catatan_rujukan' => $validated['catatan_rujukan'] ?? null, // Catatan RS
                    'done_status'     => true,  // Tandai sudah selesai diproses
                    'is_rujuk'        => true,  // Tandai sudah dirujuk
                ]
            );

            // ================== 4b. UPDATE/BUAT RIWAYAT RUJUKAN ==================
            
            // Cek apakah sudah ada riwayat rujukan
            $existingRiwayat = DB::table('riwayat_rujukans')
                ->where('rujukan_id', $rujukan->id)
                ->first();

            $now = now(); // Tanggal/waktu sekarang

            if ($existingRiwayat) {
                // Jika sudah ada riwayat, UPDATE data
                DB::table('riwayat_rujukans')
                    ->where('id', $existingRiwayat->id)
                    ->update([
                        'tindakan'      => $validated['tindakan'] ?? null,                  // Tindakan medis
                        'tekanan_darah' => $validated['riwayat_tekanan_darah'] ?? null,     // Tekanan darah
                        'updated_at'    => $now,                                             // Timestamp update
                    ]);

                $riwayatId = $existingRiwayat->id; // ID riwayat yang sudah ada
            } else {
                // Jika belum ada riwayat, INSERT data baru
                // insertGetId(): insert dan return ID yang baru dibuat
                $riwayatId = DB::table('riwayat_rujukans')->insertGetId([
                    'rujukan_id'     => $rujukan->id,                               // FK ke rujukan_rs
                    'skrining_id'    => $skrining->id,                              // FK ke skrining
                    'tanggal_datang' => $now->toDateString(),                       // Tanggal periksa
                    'tekanan_darah'  => $validated['riwayat_tekanan_darah'] ?? null, // Tekanan darah
                    'tindakan'       => $validated['tindakan'] ?? null,             // Tindakan medis
                    'created_at'     => $now,                                        // Timestamp created
                    'updated_at'     => $now,                                        // Timestamp updated
                ]);
            }

            // ================== 4c. SIMPAN RESEP OBAT ==================
            
            // Hapus semua resep lama untuk rujukan ini
            // where('rujukan_rs_id', $rujukan->id): filter per rujukan
            // delete(): hapus semua data yang match
            ResepObat::where('rujukan_rs_id', $rujukan->id)->delete();

            // Simpan resep obat baru (jika ada)
            if (!empty($validated['resep_obat'])) {
                // Loop setiap obat yang diinput
                foreach ($validated['resep_obat'] as $index => $obat) {
                    // Hanya simpan jika nama obat tidak kosong
                    if (!empty($obat)) {
                        ResepObat::create([
                            'rujukan_rs_id'      => $rujukan->id,                     // FK ke rujukan_rs (WAJIB)
                            'resep_obat'         => $obat,                            // Nama obat
                            'dosis'              => $validated['dosis'][$index] ?? null,      // Dosis obat
                            'penggunaan'         => $validated['penggunaan'][$index] ?? null, // Aturan pakai
                        ]);
                    }
                }
            }
        }); // End transaction

        // 5. Redirect dengan Flash Message
        return redirect()
            ->route('rs.skrining.show', $id)                        // Redirect ke halaman detail
            ->with('success', 'Data berhasil disimpan!');           // Flash message sukses
    }
}

/*
|--------------------------------------------------------------------------
| PENJELASAN FUNGSI-FUNGSI:
|--------------------------------------------------------------------------
|
| 1. firstOrCreate($attributes, $values)
|    - Cari data dengan kondisi $attributes
|    - Jika ada: return data existing
|    - Jika tidak: buat data baru dengan $attributes + $values
|    - Contoh: firstOrCreate(['id'=>1], ['name'=>'John'])
|
| 2. updateOrCreate($attributes, $values)
|    - Cari data dengan kondisi $attributes
|    - Jika ada: update dengan $values
|    - Jika tidak: buat data baru dengan $attributes + $values
|    - Return: Model yang di-update/create
|
| 3. collect()
|    - Buat Collection kosong
|    - Collection: array dengan method helper Laravel
|    - Contoh: collect()->isEmpty() untuk cek kosong
|
| 4. isEmpty()
|    - Cek apakah Collection kosong
|    - Return: true jika kosong, false jika ada data
|    - Contoh: $collection->isEmpty()
|
| 5. in_array($needle, $haystack)
|    - Cek apakah nilai ada dalam array
|    - Return: true jika ada, false jika tidak
|    - Contoh: in_array('a', ['a','b','c']) -> true
|
| 6. DB::transaction(function)
|    - Jalankan query dalam transaction
|    - Semua query sukses: auto commit
|    - Ada error: auto rollback
|    - Tidak perlu manual commit/rollback
|
| 7. insertGetId($data)
|    - Insert data dan return ID yang baru dibuat
|    - Untuk tabel tanpa Model Eloquent
|    - Return: integer (ID baru)
|
| 8. toDateString()
|    - Convert Carbon ke format Y-m-d
|    - Contoh: 2024-01-15
|    - Untuk simpan tanggal tanpa waktu
|
| 9. array validation rules
|    - 'field' => 'array': validasi harus array
|    - 'field.*' => 'string': setiap item dalam array harus string
|    - Untuk form dengan multiple input
|
| 10. $index dalam foreach
|     - Index/key dari array
|     - Dimulai dari 0
|     - Untuk akses array paralel (resep_obat, dosis, penggunaan)
|
|--------------------------------------------------------------------------
| KONSEP PENTING:
|--------------------------------------------------------------------------
|
| 1. EAGER LOADING
|    - Load relasi sekaligus dengan query utama
|    - with(['relasi1', 'relasi2.nested'])
|    - Hindari N+1 query problem
|
| 2. OPTIONAL() HELPER
|    - Cegah error "Trying to get property of non-object"
|    - optional($var)->property
|    - Return null jika $var null
|
| 3. NULLABLE VALIDATION
|    - Field boleh null/kosong
|    - 'field' => 'nullable|string'
|    - Jika tidak ada, pakai 'required'
|
| 4. TRANSACTION PATTERN
|    - Semua query terkait dalam 1 transaction
|    - Jaga konsistensi data
|    - Auto rollback jika ada error
|
| 5. FALLBACK PATTERN
|    - Coba cara 1, jika gagal coba cara 2
|    - if (isEmpty()) { fallback }
|    - Untuk backward compatibility
|
| 6. TRANSFORM PATTERN
|    - Modifikasi data sebelum dikirim ke view
|    - getCollection()->transform()
|    - Tambah property atau format data
|
|--------------------------------------------------------------------------
*/