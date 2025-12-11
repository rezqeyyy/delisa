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
    public function index()
    {
        $pasienNifas = PasienNifasRs::with(['pasien.user', 'pasien.skrinings', 'rs'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Transform data untuk menambahkan status_display
        $pasienNifas->getCollection()->transform(function ($pn) {
            $statusRisiko = $this->getStatusRisikoFromSkrining($pn->pasien, $pn);
            $pn->status_display = $statusRisiko['label'];
            $pn->status_type = $statusRisiko['type'];
            
            return $pn;
        });

        return view('rs.pasien-nifas.index', compact('pasienNifas'));
    }

    /**
     * Ambil status risiko dari data skrining pasien
     * PRIORITAS:
     * 1. Jika ada data skrining -> gunakan kolom KESIMPULAN dari skrining
     * 2. Jika tidak ada skrining -> gunakan status_risiko_manual dari pasien_nifas_rs
     * 3. Jika keduanya tidak ada -> return 'Tidak Berisiko'
     * 
     * @param Pasien|null $pasien
     * @param PasienNifasRs|null $pasienNifas
     * @return array
     */
    private function getStatusRisikoFromSkrining($pasien, $pasienNifas = null)
    {
        if (!$pasien) {
            return ['label' => 'Tidak Berisiko', 'type' => 'normal'];
        }

        // PRIORITAS 1: Cek data skrining
        $skrining = Skrining::where('pasien_id', $pasien->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($skrining) {
            // Gunakan kolom KESIMPULAN
            $kesimpulan = strtolower(trim($skrining->kesimpulan ?? ''));
            
            // 🔥 FIX: Cek "tidak beresiko" dulu sebelum cek "beresiko"
            if (str_contains($kesimpulan, 'tidak beresiko') || str_contains($kesimpulan, 'tidak berisiko')) {
                return ['label' => 'Tidak Berisiko', 'type' => 'normal'];
            } elseif (str_contains($kesimpulan, 'beresiko') || str_contains($kesimpulan, 'berisiko')) {
                return ['label' => 'Beresiko', 'type' => 'beresiko'];
            } else {
                // Default jika kesimpulan tidak sesuai format
                return ['label' => 'Tidak Berisiko', 'type' => 'normal'];
            }
        }

        // PRIORITAS 2: Tidak ada skrining, cek status_risiko_manual
        if ($pasienNifas && !empty($pasienNifas->status_risiko_manual)) {
            $statusManual = $pasienNifas->status_risiko_manual;
            
            $labelMap = [
                'beresiko' => 'Beresiko',
                'normal' => 'Tidak Berisiko'
            ];

            return [
                'label' => $labelMap[$statusManual] ?? 'Tidak Berisiko',
                'type' => $statusManual
            ];
        }

        // PRIORITAS 3: Default
        return ['label' => 'Tidak Berisiko', 'type' => 'normal'];
    }

    /**
     * Show form create pasien nifas
     */
    public function create()
    {
        return view('rs.pasien-nifas.create');
    }

    /**
     * Cek NIK - API endpoint untuk mencari pasien berdasarkan NIK
     */
    public function cekNik(Request $request)
    {
        $nik = $request->input('nik');

        // Validasi NIK
        if (!$nik || strlen($nik) !== 16) {
            return response()->json([
                'found' => false,
                'message' => 'NIK tidak valid. Harus 16 digit.'
            ]);
        }

        try {
            // Cari pasien berdasarkan NIK dengan relasi user dan skrining
            $pasien = Pasien::where('nik', $nik)
                ->with(['user', 'skrinings' => function($q) {
                    $q->orderBy('created_at', 'desc')->limit(1);
                }])
                ->first();

            if ($pasien) {
                // Ambil status risiko dari skrining
                $statusRisiko = $this->getStatusRisikoFromSkrining($pasien);

                // Pasien ditemukan - return data lengkap untuk auto-fill
                return response()->json([
                    'found' => true,
                    'message' => 'Pasien ditemukan',
                    'pasien' => [
                        'id'                    => $pasien->id,
                        'nik'                   => $pasien->nik,
                        'nama'                  => $pasien->user->name ?? '',
                        'no_telepon'            => $pasien->user->phone ?? '',
                        
                        // Data Wilayah
                        'provinsi'              => $pasien->PProvinsi ?? '',
                        'kota'                  => $pasien->PKabupaten ?? '',
                        'kecamatan'             => $pasien->PKecamatan ?? '',
                        'kelurahan'             => $pasien->PWilayah ?? '',
                        
                        // Data Alamat
                        'domisili'              => ($pasien->address ?? ($pasien->user->address ?? null)) ?? $this->buildDomisili($pasien),
                        'rt'                    => $pasien->rt ?? '',
                        'rw'                    => $pasien->rw ?? '',
                        'kode_pos'              => $pasien->kode_pos ?? '',
                        
                        // Data Pribadi
                        'tempat_lahir'          => $pasien->tempat_lahir ?? '',
                        'tanggal_lahir'         => $pasien->tanggal_lahir ?? '',
                        'status_perkawinan'     => $pasien->status_perkawinan ?? '',
                        'pekerjaan'             => $pasien->pekerjaan ?? '',
                        'pendidikan'            => $pasien->pendidikan ?? '',
                        
                        // Data Kesehatan
                        'pembiayaan_kesehatan'  => $pasien->pembiayaan_kesehatan ?? '',
                        'golongan_darah'        => $pasien->golongan_darah ?? '',
                        'no_jkn'                => $pasien->no_jkn ?? '',
                        
                        // Status Risiko
                        'status_risiko'         => $statusRisiko['label'],
                        'status_type'           => $statusRisiko['type'],
                        'has_skrining'          => $pasien->skrinings->count() > 0,
                    ]
                ]);
            }

            // Pasien tidak ditemukan
            return response()->json([
                'found' => false,
                'message' => 'Pasien dengan NIK tersebut tidak ditemukan. Silakan isi data baru.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error Cek NIK: ' . $e->getMessage());

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pasien' => 'required|string|max:255',
            'nik'         => 'required|digits:16',
            'no_telepon'  => 'required|string|max:20',
            'provinsi'    => 'required|string|max:100',
            'kota'        => 'required|string|max:100',
            'kecamatan'   => 'required|string|max:100',
            'kelurahan'   => 'required|string|max:100',
            'domisili'    => 'required|string',
            
            // Status risiko manual - hanya beresiko atau normal (tanpa waspada)
            'status_risiko_manual' => 'nullable|in:beresiko,normal',
            
            // Field tambahan
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'status_perkawinan' => 'nullable|string|max:50',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'kode_pos' => 'nullable|string|max:10',
            'pekerjaan' => 'nullable|string|max:100',
            'pendidikan' => 'nullable|string|max:50',
            'pembiayaan_kesehatan' => 'nullable|string|max:50',
            'golongan_darah' => 'nullable|string|max:5',
            'no_jkn' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // Cek apakah pasien dengan NIK ini sudah ada
            $existingPasien = Pasien::where('nik', $validated['nik'])->first();

            if ($existingPasien) {
                $pasien = $existingPasien;

                // Update phone di tabel users
                if ($existingPasien->user) {
                    $existingPasien->user->update([
                        'phone' => $validated['no_telepon'],
                    ]);
                }

                // Mapping status_perkawinan ke boolean (true=Menikah, false=Belum Menikah)
                $spRaw = $validated['status_perkawinan'] ?? null;
                $spLower = is_string($spRaw) ? strtolower(trim($spRaw)) : '';
                $statusPerkawinanBool = is_null($spRaw) ? null : (
                    strpos($spLower, 'menikah') !== false ||
                    strpos($spLower, 'kawin') !== false ||
                    $spLower === 'married'
                );

                // Update data wilayah pasien + alamat
                $updateData = [
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                    'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                    'status_perkawinan' => $statusPerkawinanBool,
                    'rt' => $validated['rt'] ?? null,
                    'rw' => $validated['rw'] ?? null,
                    'kode_pos' => $validated['kode_pos'] ?? null,
                    'pekerjaan' => $validated['pekerjaan'] ?? null,
                    'pendidikan' => $validated['pendidikan'] ?? null,
                    'pembiayaan_kesehatan' => $validated['pembiayaan_kesehatan'] ?? null,
                    'golongan_darah' => $validated['golongan_darah'] ?? null,
                    'no_jkn' => $validated['no_jkn'] ?? null,
                ];

                if (Schema::hasColumn('pasiens', 'address')) {
                    $updateData['address'] = $validated['domisili'];
                } else if ($existingPasien->user) {
                    $existingPasien->user->update(['address' => $validated['domisili']]);
                }

                $existingPasien->update($updateData);
            } else {
                // Buat user + pasien baru
                $roleId = DB::table('roles')
                    ->where('nama_role', 'pasien')
                    ->first();

                if (!$roleId) {
                    throw new \Exception('Role "pasien" tidak ditemukan di database');
                }

                $baseEmail = $validated['nik'] . '@pasien.delisa.id';
                $emailExists = User::where('email', $baseEmail)->exists();
                $email = $emailExists 
                    ? $validated['nik'] . '.' . time() . '@pasien.delisa.id'
                    : $baseEmail;

                $user = User::create([
                    'name'     => $validated['nama_pasien'],
                    'email'    => $email,
                    'password' => bcrypt('password'),
                    'role_id'  => $roleId->id,
                    'phone'    => $validated['no_telepon'],
                    'status'   => true,
                ]);

                // Mapping status_perkawinan ke boolean (true=Menikah, false=Belum Menikah)
                $spRaw = $validated['status_perkawinan'] ?? null;
                $spLower = is_string($spRaw) ? strtolower(trim($spRaw)) : '';
                $statusPerkawinanBool = is_null($spRaw) ? null : (
                    strpos($spLower, 'menikah') !== false ||
                    strpos($spLower, 'kawin') !== false ||
                    $spLower === 'married'
                );

                $pasienData = [
                    'user_id'    => $user->id,
                    'nik'        => $validated['nik'],
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                    'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                    'status_perkawinan' => $statusPerkawinanBool,
                    'rt' => $validated['rt'] ?? null,
                    'rw' => $validated['rw'] ?? null,
                    'kode_pos' => $validated['kode_pos'] ?? null,
                    'pekerjaan' => $validated['pekerjaan'] ?? null,
                    'pendidikan' => $validated['pendidikan'] ?? null,
                    'pembiayaan_kesehatan' => $validated['pembiayaan_kesehatan'] ?? null,
                    'golongan_darah' => $validated['golongan_darah'] ?? null,
                    'no_jkn' => $validated['no_jkn'] ?? null,
                ];

                if (Schema::hasColumn('pasiens', 'address')) {
                    $pasienData['address'] = $validated['domisili'];
                } else {
                    $user->update(['address' => $validated['domisili']]);
                }

                $pasien = Pasien::create($pasienData);
            }

            // Ambil rs_id dari user RS yang sedang login
            $rs_id = $this->getRsId();

            // Cek apakah pasien nifas ini sudah terdaftar di RS ini
            $existingNifas = PasienNifasRs::where('pasien_id', $pasien->id)
                ->where('rs_id', $rs_id)
                ->first();

            if ($existingNifas) {
                DB::commit();

                return redirect()
                    ->route('rs.pasien-nifas.show', $existingNifas->id)
                    ->with('info', 'Pasien sudah terdaftar. Silakan tambah data anak.');
            }

            // Cek skrining
            $hasSkrining = Skrining::where('pasien_id', $pasien->id)->exists();
            
            // Jika TIDAK punya skrining, status_risiko_manual WAJIB
            if (!$hasSkrining && empty($request->input('status_risiko_manual'))) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->withErrors(['status_risiko_manual' => 'Status risiko wajib dipilih karena pasien belum memiliki data skrining.']);
            }

            // Buat dummy skrining otomatis jika belum ada
            if (!$hasSkrining && !empty($request->input('status_risiko_manual'))) {
                $statusManual = $request->input('status_risiko_manual');
                
                // Mapping status ke kesimpulan
                $kesimpulan = ($statusManual === 'beresiko') ? 'Beresiko' : 'Tidak beresiko';
                
                // Isi jumlah risiko tinggi hanya jika beresiko
                $jumlahResikoTinggi = ($statusManual === 'beresiko') ? 1 : 0;
                
                // Ambil puskesmas ID (wajib diisi)
                $puskesmasId = DB::table('puskesmas')->orderBy('id')->value('id');
                
                // Jika tidak ada puskesmas, buat dummy
                if (!$puskesmasId) {
                    $puskesmasId = DB::table('puskesmas')->insertGetId([
                        'nama_puskesmas' => 'Input Manual dari RS',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                Skrining::create([
                    'pasien_id' => $pasien->id,
                    'puskesmas_id' => $puskesmasId,
                    'jumlah_resiko_tinggi' => $jumlahResikoTinggi,
                    'jumlah_resiko_sedang' => 0,
                    'kesimpulan' => $kesimpulan,
                    'step_form' => null,
                    'tindak_lanjut' => false,
                    'checked_status' => true,
                ]);
                
                Log::info('Created dummy skrining', [
                    'pasien_id' => $pasien->id,
                    'status' => $statusManual,
                    'kesimpulan' => $kesimpulan,
                    'puskesmas_id' => $puskesmasId
                ]);
            }

            // Buat entri pasien nifas baru
            $pasienNifasData = [
                'rs_id'               => $rs_id,
                'pasien_id'           => $pasien->id,
                'tanggal_mulai_nifas' => now(),
            ];

            // Tetap simpan status_risiko_manual sebagai backup
            if (!$hasSkrining && !empty($request->input('status_risiko_manual'))) {
                $pasienNifasData['status_risiko_manual'] = $request->input('status_risiko_manual');
            }

            $pasienNifas = PasienNifasRs::create($pasienNifasData);

            DB::commit();

            return redirect()
                ->route('rs.pasien-nifas.show', $pasienNifas->id)
                ->with('success', 'Data pasien nifas berhasil ditambahkan! Silakan tambah data anak.');
            
        } catch (\Exception $e) {
            DB::rollBack();
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
    public function show($id)
    {
        $pasienNifas = PasienNifasRs::with(['pasien.user', 'pasien.skrinings', 'rs', 'anakPasien'])
            ->findOrFail($id);

        $statusRisiko = $this->getStatusRisikoFromSkrining($pasienNifas->pasien, $pasienNifas);
        $pasienNifas->status_display = $statusRisiko['label'];
        $pasienNifas->status_type = $statusRisiko['type'];

        return view('rs.pasien-nifas.edit', compact('pasienNifas'));
    }

    /**
     * Store data anak pasien
     */
    public function storeAnakPasien(Request $request, $id)
    {
        $pasienNifas = PasienNifasRs::with('pasien')->findOrFail($id);
        
        // Cek apakah pasien beresiko
        $statusRisiko = $this->getStatusRisikoFromSkrining($pasienNifas->pasien, $pasienNifas);
        $isBeresiko = ($statusRisiko['type'] === 'beresiko');

        // Validasi dasar
        $rules = [
            'anak_ke'                    => 'required|integer|min:1',
            'tanggal_lahir'              => 'required|date',
            'jenis_kelamin'              => 'required|in:Laki-laki,Perempuan',
            'nama_anak'                  => 'nullable|string|max:255',
            'usia_kehamilan_saat_lahir'  => 'required|string',
            'berat_lahir_anak'           => 'required|numeric|min:0',
            'panjang_lahir_anak'         => 'required|numeric|min:0',
            'lingkar_kepala_anak'        => 'required|numeric|min:0',
            'memiliki_buku_kia'          => 'required|boolean',
            'buku_kia_bayi_kecil'        => 'required|boolean',
            'imd'                        => 'required|boolean',
            'riwayat_penyakit'           => 'nullable|array',
            'keterangan_masalah_lain'    => 'nullable|string',
        ];

        // Tambahkan validasi kondisi ibu jika pasien beresiko
        if ($isBeresiko) {
            $rules['kondisi_ibu'] = 'required|in:aman,perlu_tindak_lanjut';
            $rules['catatan_kondisi_ibu'] = 'required|string';
        } else {
            $rules['kondisi_ibu'] = 'nullable|in:aman,perlu_tindak_lanjut';
            $rules['catatan_kondisi_ibu'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        try {
            AnakPasien::create([
                'nifas_id'                  => $pasienNifas->id,
                'anak_ke'                   => $validated['anak_ke'],
                'tanggal_lahir'             => $validated['tanggal_lahir'],
                'jenis_kelamin'             => $validated['jenis_kelamin'],
                'nama_anak'                 => $validated['nama_anak'] ?? 'Anak ke-' . $validated['anak_ke'],
                'usia_kehamilan_saat_lahir' => $validated['usia_kehamilan_saat_lahir'],
                'berat_lahir_anak'          => $validated['berat_lahir_anak'],
                'panjang_lahir_anak'        => $validated['panjang_lahir_anak'],
                'lingkar_kepala_anak'       => $validated['lingkar_kepala_anak'],
                'memiliki_buku_kia'         => $validated['memiliki_buku_kia'],
                'buku_kia_bayi_kecil'       => $validated['buku_kia_bayi_kecil'],
                'imd'                       => $validated['imd'],
                'riwayat_penyakit'          => $validated['riwayat_penyakit'] ?? [],
                'keterangan_masalah_lain'   => $validated['keterangan_masalah_lain'],
                'kondisi_ibu'               => $validated['kondisi_ibu'] ?? null,
                'catatan_kondisi_ibu'       => $validated['catatan_kondisi_ibu'] ?? null,
            ]);

            return redirect()
                ->route('rs.pasien-nifas.detail', $id)
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
    public function detail($id)
    {
        $pasienNifas = PasienNifasRs::with([
            'pasien.user',
            'pasien.skrinings',
            'rs',
            'anakPasien'
        ])->findOrFail($id);

        $statusRisiko = $this->getStatusRisikoFromSkrining($pasienNifas->pasien, $pasienNifas);
        $pasienNifas->status_display = $statusRisiko['label'];
        $pasienNifas->status_type = $statusRisiko['type'];

        $anakPasien = $pasienNifas->anakPasien->first();

        return view('rs.pasien-nifas.show', compact('pasienNifas', 'anakPasien'));
    }

    /**
     * Download PDF single pasien nifas
     */
    public function downloadSinglePDF($id)
    {
        try {
            $pasienNifas = PasienNifasRs::with([
                'pasien.user',
                'pasien.skrinings',
                'rs',
                'anakPasien'
            ])->findOrFail($id);

            $statusRisiko = $this->getStatusRisikoFromSkrining($pasienNifas->pasien, $pasienNifas);
            $pasienNifas->status_display = $statusRisiko['label'];
            $pasienNifas->status_type = $statusRisiko['type'];

            $pdf = Pdf::loadView('rs.pasien-nifas.single-pdf', compact('pasienNifas'));
            $pdf->setPaper('A4', 'portrait');
            
            $namaPasien = $pasienNifas->pasien->user->name ?? 'Pasien';
            $filename = 'Data_Nifas_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $namaPasien) . '_' . date('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error Download Single PDF: ' . $e->getMessage());
            
            return back()
                ->with('error', 'Gagal mengunduh PDF: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF data pasien nifas
     */
    public function downloadPDF()
    {
        try {
            $pasienNifas = PasienNifasRs::with(['pasien.user', 'pasien.skrinings', 'rs'])
                ->orderBy('created_at', 'desc')
                ->get();

            $pasienNifas->transform(function ($pn) {
                $statusRisiko = $this->getStatusRisikoFromSkrining($pn->pasien, $pn);
                $pn->status_display = $statusRisiko['label'];
                $pn->status_type = $statusRisiko['type'];
                return $pn;
            });

            $pdf = Pdf::loadView('rs.pasien-nifas.pdf', compact('pasienNifas'));

            return $pdf->download('data-pasien-nifas-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal mengunduh PDF: ' . $e->getMessage());
        }
    }
}