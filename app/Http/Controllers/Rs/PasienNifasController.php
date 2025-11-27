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

        // Transform data untuk menambahkan status_display berdasarkan skrining
        $pasienNifas->getCollection()->transform(function ($pn) {
            // Ambil status risiko dari skrining pasien
            $statusRisiko = $this->getStatusRisikoFromSkrining($pn->pasien);
            $pn->status_display = $statusRisiko['label'];
            $pn->status_type = $statusRisiko['type']; // 'beresiko', 'waspada', 'normal'
            
            return $pn;
        });

        return view('rs.pasien-nifas.index', compact('pasienNifas'));
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
    public function create()
    {
        return view('rs.pasien-nifas.create');
    }

    /**
     * Cek NIK - API endpoint untuk mencari pasien berdasarkan NIK
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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

                // Pasien ditemukan - return data untuk auto-fill
                return response()->json([
                    'found' => true,
                    'message' => 'Pasien ditemukan',
                    'pasien' => [
                        'id'            => $pasien->id,
                        'nik'           => $pasien->nik,
                        'nama'          => $pasien->user->name ?? '',
                        'no_telepon'    => $pasien->user->phone ?? '',
                        'provinsi'      => $pasien->PProvinsi ?? '',
                        'kota'          => $pasien->PKabupaten ?? '',
                        'kecamatan'     => $pasien->PKecamatan ?? '',
                        'kelurahan'     => $pasien->PWilayah ?? '',
                        'domisili'      => $this->buildDomisili($pasien),
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

                // Update data wilayah pasien
                $existingPasien->update([
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                ]);
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
                ]);

                $pasien = Pasien::create([
                    'user_id'    => $user->id,
                    'nik'        => $validated['nik'],
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                ]);
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

            // Buat entri pasien nifas baru
            $pasienNifas = PasienNifasRs::create([
                'rs_id'               => $rs_id,
                'pasien_id'           => $pasien->id,
                'tanggal_mulai_nifas' => now(),
            ]);

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
    public function show($id)
    {
        $pasienNifas = PasienNifasRs::with(['pasien.user', 'pasien.skrinings', 'rs', 'anakPasien'])
            ->findOrFail($id);

        // Ambil status risiko
        $statusRisiko = $this->getStatusRisikoFromSkrining($pasienNifas->pasien);
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
        $statusRisiko = $this->getStatusRisikoFromSkrining($pasienNifas->pasien);
        $isBeresiko = in_array($statusRisiko['type'], ['beresiko', 'waspada']);

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
                // Kolom baru untuk kondisi ibu
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

        // Ambil status risiko
        $statusRisiko = $this->getStatusRisikoFromSkrining($pasienNifas->pasien);
        $pasienNifas->status_display = $statusRisiko['label'];
        $pasienNifas->status_type = $statusRisiko['type'];

        $anakPasien = $pasienNifas->anakPasien->first();

        return view('rs.pasien-nifas.show', compact('pasienNifas', 'anakPasien'));
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

            // Transform untuk menambahkan status
            $pasienNifas->transform(function ($pn) {
                $statusRisiko = $this->getStatusRisikoFromSkrining($pn->pasien);
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