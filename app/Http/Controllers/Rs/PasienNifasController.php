<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\PasienNifasRs;
use App\Models\AnakPasien;
use App\Models\Pasien;
use App\Models\User;
use App\Models\Skrining;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PasienNifasController extends Controller
{
    /**
     * Display list of pasien nifas
     */
    public function index() // Menampilkan daftar pasien nifas dengan pagination, termasuk status risiko dari skrining
    {
        $pasienNifas = PasienNifasRs::with(['pasien.user', 'pasien.skrinings', 'rs']) // Eager load relasi pasien, user, skrining, dan RS
            ->orderBy('created_at', 'desc') // Urutkan berdasarkan terbaru
            ->paginate(10); // Batasi 10 data per halaman

        // Transform data untuk menambahkan status_display berdasarkan skrining
        $pasienNifas->getCollection()->transform(function ($pn) {
            // Ambil status risiko dari skrining pasien
            $statusRisiko = $this->getStatusRisikoFromSkrining($pn->pasien); // Hitung status risiko
            $pn->status_display = $statusRisiko['label']; // Label: Beresiko/Waspada/Tidak Berisiko
            $pn->status_type = $statusRisiko['type']; // 'beresiko', 'waspada', 'normal'
            
            return $pn;
        });

        return view('rs.pasien-nifas.index', compact('pasienNifas')); // Kembalikan view dengan data
    }

    /**
     * Ambil status risiko dari data skrining pasien
     * 
     * @param Pasien|null $pasien
     * @return array
     */
    private function getStatusRisikoFromSkrining($pasien)
    {
        if (!$pasien) {
            return ['label' => 'Tidak Berisiko', 'type' => 'normal'];
        }

        // Ambil skrining terbaru dari pasien
        $skrining = Skrining::where('pasien_id', $pasien->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$skrining) {
            return ['label' => 'Tidak Berisiko', 'type' => 'normal'];
        }

        // Cek berdasarkan jumlah_resiko_tinggi dan jumlah_resiko_sedang
        $resikoTinggi = $skrining->jumlah_resiko_tinggi ?? 0;
        $resikoSedang = $skrining->jumlah_resiko_sedang ?? 0;

        // Cek juga dari kolom kesimpulan atau status_pre_eklampsia
        $kesimpulan = strtolower(trim($skrining->kesimpulan ?? ''));
        $statusPE = strtolower(trim($skrining->status_pre_eklampsia ?? ''));

        // Kondisi BERESIKO (Risiko Tinggi)
        $isHighRisk = $resikoTinggi > 0
            || in_array($kesimpulan, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi'])
            || in_array($statusPE, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi']);

        // Kondisi WASPADA (Risiko Sedang)
        $isMediumRisk = $resikoSedang > 0
            || in_array($kesimpulan, ['waspada', 'menengah', 'sedang', 'risiko sedang'])
            || in_array($statusPE, ['waspada', 'menengah', 'sedang', 'risiko sedang']);

        if ($isHighRisk) {
            return ['label' => 'Beresiko', 'type' => 'beresiko'];
        } elseif ($isMediumRisk) {
            return ['label' => 'Waspada', 'type' => 'waspada'];
        } else {
            return ['label' => 'Tidak Berisiko', 'type' => 'normal'];
        }
    }

    /**
     * Show form create pasien nifas
     */
    public function create() // Menampilkan form untuk mendaftarkan pasien nifas baru
    {
        return view('rs.pasien-nifas.create'); // Kembalikan view form create
    }

    /**
     * Cek NIK - API endpoint untuk mencari pasien berdasarkan NIK
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cekNik(Request $request) // Endpoint AJAX untuk cek NIK dan auto-fill data pasien jika ditemukan
    {
        $nik = $request->input('nik'); // Ambil NIK dari request

        // Validasi NIK
        if (!$nik || strlen($nik) !== 16) { // NIK harus 16 digit
            return response()->json([
                'found' => false,
                'message' => 'NIK tidak valid. Harus 16 digit.'
            ]);
        }

        try {
            // Cari pasien berdasarkan NIK dengan relasi user dan skrining
            $pasien = Pasien::where('nik', $nik)
                ->with(['user', 'skrinings' => function($q) {
                    $q->orderBy('created_at', 'desc')->limit(1); // Ambil skrining terbaru saja
                }])
                ->first();

            if ($pasien) { // Jika pasien ditemukan
                // Ambil status risiko dari skrining
                $statusRisiko = $this->getStatusRisikoFromSkrining($pasien);

                // Pasien ditemukan - return data untuk auto-fill
                return response()->json([
                    'found' => true,
                    'message' => 'Pasien ditemukan',
                    'pasien' => [ // Data untuk mengisi form secara otomatis
                        'id'            => $pasien->id,
                        'nik'           => $pasien->nik,
                        'nama'          => $pasien->user->name ?? '',
                        'no_telepon'    => $pasien->user->phone ?? '',
                        'provinsi'      => $pasien->PProvinsi ?? '',
                        'kota'          => $pasien->PKabupaten ?? '',
                        'kecamatan'     => $pasien->PKecamatan ?? '',
                        'kelurahan'     => $pasien->PWilayah ?? '',
                        'domisili'      => ($pasien->address ?? $this->buildDomisili($pasien)),
                        'status_risiko' => $statusRisiko['label'],
                        'status_type'   => $statusRisiko['type'],
                        'has_skrining'  => $pasien->skrinings->count() > 0,
                    ]
                ]);
            }

            // Pasien tidak ditemukan
            return response()->json([
                'found' => false,
                'message' => 'Pasien dengan NIK tersebut tidak ditemukan. Silakan isi data baru.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error Cek NIK: ' . $e->getMessage()); // Log error untuk debugging

            return response()->json([
                'found' => false,
                'message' => 'Terjadi kesalahan saat mencari data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build domisili string dari data pasien
     */
    private function buildDomisili($pasien)
    {
        $parts = [];

        if (!empty($pasien->rt)) {
            $parts[] = 'RT ' . $pasien->rt;
        }
        if (!empty($pasien->rw)) {
            $parts[] = 'RW ' . $pasien->rw;
        }
        if (!empty($pasien->PWilayah)) {
            $parts[] = 'Kel. ' . $pasien->PWilayah;
        }
        if (!empty($pasien->PKecamatan)) {
            $parts[] = 'Kec. ' . $pasien->PKecamatan;
        }

        return implode(', ', $parts);
    }

    /**
     * Simpan data pasien nifas baru
     */
    public function store(Request $request) // Menyimpan data pasien nifas baru (buat user+pasien baru atau update existing)
    {
        $validated = $request->validate([ // Validasi input form
            'nama_pasien' => 'required|string|max:255',
            'nik'         => 'required|digits:16',
            'no_telepon'  => 'required|string|max:20',
            'provinsi'    => 'required|string|max:100',
            'kota'        => 'required|string|max:100',
            'kecamatan'   => 'required|string|max:100',
            'kelurahan'   => 'required|string|max:100',
            'domisili'    => 'required|string',
        ]);

        try {
            DB::beginTransaction(); // Mulai transaction untuk konsistensi data

            // Cek apakah pasien dengan NIK ini sudah ada
            $existingPasien = Pasien::where('nik', $validated['nik'])->first();

            if ($existingPasien) { // Jika pasien sudah ada, update datanya
                $pasien = $existingPasien;

                // Update phone di tabel users
                if ($existingPasien->user) {
                    $existingPasien->user->update([
                        'phone' => $validated['no_telepon'],
                    ]);
                }

                // Update data wilayah pasien + alamat
                $updateData = [
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                ];

                if (Schema::hasColumn('pasiens', 'address')) { // Cek apakah kolom address ada
                    $updateData['address'] = $validated['domisili'];
                } else if ($existingPasien->user) { // Jika tidak, simpan di tabel users
                    $existingPasien->user->update(['address' => $validated['domisili']]);
                }

                $existingPasien->update($updateData);
            } else { // Jika pasien belum ada, buat baru
                // Buat user + pasien baru
                $roleId = DB::table('roles')
                    ->where('nama_role', 'pasien')
                    ->first();

                if (!$roleId) { // Pastikan role pasien ada
                    throw new \Exception('Role "pasien" tidak ditemukan di database');
                }

                $baseEmail = $validated['nik'] . '@pasien.delisa.id'; // Generate email otomatis dari NIK
                $emailExists = User::where('email', $baseEmail)->exists();
                $email = $emailExists 
                    ? $validated['nik'] . '.' . time() . '@pasien.delisa.id' // Tambah timestamp jika email sudah ada
                    : $baseEmail;

                $user = User::create([ // Buat user baru
                    'name'     => $validated['nama_pasien'],
                    'email'    => $email,
                    'password' => bcrypt('password'), // Password default
                    'role_id'  => $roleId->id,
                    'phone'    => $validated['no_telepon'],
                ]);

                $pasienData = [ // Data untuk tabel pasiens
                    'user_id'    => $user->id,
                    'nik'        => $validated['nik'],
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                ];

                if (Schema::hasColumn('pasiens', 'address')) {
                    $pasienData['address'] = $validated['domisili'];
                } else {
                    $user->update(['address' => $validated['domisili']]);
                }

                $pasien = Pasien::create($pasienData); // Buat record pasien baru
            }

            // Ambil rs_id dari user RS yang sedang login
            $rs_id = $this->getRsId();

            // Cek apakah pasien nifas ini sudah terdaftar di RS ini
            $existingNifas = PasienNifasRs::where('pasien_id', $pasien->id)
                ->where('rs_id', $rs_id)
                ->first();

            if ($existingNifas) { // Jika sudah terdaftar, redirect ke halaman show
                DB::commit();

                return redirect()
                    ->route('rs.pasien-nifas.show', $existingNifas->id)
                    ->with('info', 'Pasien sudah terdaftar. Silakan tambah data anak.');
            }

            // Buat entri pasien nifas baru
            $pasienNifas = PasienNifasRs::create([
                'rs_id'               => $rs_id,
                'pasien_id'           => $pasien->id,
                'tanggal_mulai_nifas' => now(), // Set tanggal mulai nifas = sekarang
            ]);

            DB::commit(); // Commit transaction jika semua berhasil

            return redirect()
                ->route('rs.pasien-nifas.show', $pasienNifas->id)
                ->with('success', 'Data pasien nifas berhasil ditambahkan! Silakan tambah data anak.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback jika ada error

            Log::error('Error Create Pasien Nifas: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get RS ID from authenticated user
     */
    private function getRsId()
    {
        $user = Auth::user();

        if (!$user) {
            throw new \RuntimeException('User belum login.');
        }

        // Cek berbagai kemungkinan relasi RS
        if (!empty($user->rumah_sakit_id)) {
            return $user->rumah_sakit_id;
        }

        if (!empty($user->rs_id)) {
            return $user->rs_id;
        }

        if (method_exists($user, 'rumahSakit')) {
            $rs = $user->rumahSakit()->first();
            if ($rs) {
                return $rs->id;
            }
        }

        // Fallback: cari dari tabel rumah_sakits
        $rsId = DB::table('rumah_sakits')
            ->where('user_id', $user->id)
            ->value('id');

        if ($rsId) {
            return $rsId;
        }

        throw new \RuntimeException('Data Rumah Sakit untuk user ini tidak ditemukan.');
    }

    /**
     * Display form tambah data anak
     */
    public function show($id) // Menampilkan detail pasien nifas dan form untuk menambah data anak
    {
        $pasienNifas = PasienNifasRs::with(['pasien.user', 'pasien.skrinings', 'rs', 'anakPasien']) // Load semua relasi
            ->findOrFail($id); // Throw 404 jika tidak ditemukan

        // Ambil status risiko
        $statusRisiko = $this->getStatusRisikoFromSkrining($pasienNifas->pasien);
        $pasienNifas->status_display = $statusRisiko['label']; // Tambahkan label status
        $pasienNifas->status_type = $statusRisiko['type']; // Tambahkan type status

        return view('rs.pasien-nifas.edit', compact('pasienNifas')); // View untuk edit/tambah anak
    }

    /**
     * Store data anak pasien
     */
    public function storeAnakPasien(Request $request, $id) // Menyimpan data anak dari pasien nifas
    {
        $pasienNifas = PasienNifasRs::with('pasien')->findOrFail($id); // Ambil data pasien nifas
        
        // Cek apakah pasien beresiko
        $statusRisiko = $this->getStatusRisikoFromSkrining($pasienNifas->pasien);
        $isBeresiko = in_array($statusRisiko['type'], ['beresiko', 'waspada']); // True jika beresiko atau waspada

        // Validasi dasar
        $rules = [
            'anak_ke'                    => 'required|integer|min:1', // Urutan anak
            'tanggal_lahir'              => 'required|date',
            'jenis_kelamin'              => 'required|in:Laki-laki,Perempuan',
            'nama_anak'                  => 'nullable|string|max:255',
            'usia_kehamilan_saat_lahir'  => 'required|string', // Usia kehamilan dalam minggu
            'berat_lahir_anak'           => 'required|numeric|min:0', // Dalam gram
            'panjang_lahir_anak'         => 'required|numeric|min:0', // Dalam cm
            'lingkar_kepala_anak'        => 'required|numeric|min:0', // Dalam cm
            'memiliki_buku_kia'          => 'required|boolean',
            'buku_kia_bayi_kecil'        => 'required|boolean',
            'imd'                        => 'required|boolean', // Inisiasi Menyusu Dini
            'riwayat_penyakit'           => 'nullable|array',
            'keterangan_masalah_lain'    => 'nullable|string',
        ];

        // Tambahkan validasi kondisi ibu jika pasien beresiko
        if ($isBeresiko) { // Wajib isi kondisi ibu jika pasien beresiko
            $rules['kondisi_ibu'] = 'required|in:aman,perlu_tindak_lanjut';
            $rules['catatan_kondisi_ibu'] = 'required|string';
        } else { // Opsional jika pasien tidak beresiko
            $rules['kondisi_ibu'] = 'nullable|in:aman,perlu_tindak_lanjut';
            $rules['catatan_kondisi_ibu'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        try {
            AnakPasien::create([ // Simpan data anak ke database
                'nifas_id'                  => $pasienNifas->id, // Foreign key ke pasien_nifas
                'anak_ke'                   => $validated['anak_ke'],
                'tanggal_lahir'             => $validated['tanggal_lahir'],
                'jenis_kelamin'             => $validated['jenis_kelamin'],
                'nama_anak'                 => $validated['nama_anak'] ?? 'Anak ke-' . $validated['anak_ke'], // Default nama jika kosong
                'usia_kehamilan_saat_lahir' => $validated['usia_kehamilan_saat_lahir'],
                'berat_lahir_anak'          => $validated['berat_lahir_anak'],
                'panjang_lahir_anak'        => $validated['panjang_lahir_anak'],
                'lingkar_kepala_anak'       => $validated['lingkar_kepala_anak'],
                'memiliki_buku_kia'         => $validated['memiliki_buku_kia'],
                'buku_kia_bayi_kecil'       => $validated['buku_kia_bayi_kecil'],
                'imd'                       => $validated['imd'],
                'riwayat_penyakit'          => $validated['riwayat_penyakit'] ?? [], // Default array kosong
                'keterangan_masalah_lain'   => $validated['keterangan_masalah_lain'],
                // Kolom baru untuk kondisi ibu
                'kondisi_ibu'               => $validated['kondisi_ibu'] ?? null,
                'catatan_kondisi_ibu'       => $validated['catatan_kondisi_ibu'] ?? null,
            ]);

            return redirect()
                ->route('rs.pasien-nifas.detail', $id) // Redirect ke halaman detail
                ->with('success', 'Data anak berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error Store Anak Pasien: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display detail readonly pasien nifas dan data anak
     */
    public function detail($id) // Menampilkan detail lengkap pasien nifas dan anak (read-only)
    {
        $pasienNifas = PasienNifasRs::with([ // Load semua relasi yang dibutuhkan
            'pasien.user',
            'pasien.skrinings',
            'rs',
            'anakPasien'
        ])->findOrFail($id);

        // Ambil status risiko
        $statusRisiko = $this->getStatusRisikoFromSkrining($pasienNifas->pasien);
        $pasienNifas->status_display = $statusRisiko['label'];
        $pasienNifas->status_type = $statusRisiko['type'];

        $anakPasien = $pasienNifas->anakPasien->first(); // Ambil data anak pertama (jika ada)

        return view('rs.pasien-nifas.show', compact('pasienNifas', 'anakPasien')); // View detail read-only
    }

    /**
     * Download PDF data pasien nifas
     */
    public function downloadPDF() // Generate dan download PDF daftar semua pasien nifas
    {
        try {
            $pasienNifas = PasienNifasRs::with(['pasien.user', 'pasien.skrinings', 'rs']) // Ambil semua data
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform untuk menambahkan status
            $pasienNifas->transform(function ($pn) { // Tambahkan status risiko ke setiap record
                $statusRisiko = $this->getStatusRisikoFromSkrining($pn->pasien);
                $pn->status_display = $statusRisiko['label'];
                $pn->status_type = $statusRisiko['type'];
                return $pn;
            });

            $pdf = Pdf::loadView('rs.pasien-nifas.pdf', compact('pasienNifas')); // Generate PDF dari view

            return $pdf->download('data-pasien-nifas-' . date('Y-m-d') . '.pdf'); // Download dengan nama file + tanggal
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal mengunduh PDF: ' . $e->getMessage());
        }
    }
}