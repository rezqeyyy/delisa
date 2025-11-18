<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\PasienNifas;
use App\Models\AnakPasien;
use App\Models\Pasien;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PasienNifasController extends Controller
{
    /**
     * Display list of pasien nifas
     */
    public function index()
    {
        $pasienNifas = PasienNifas::with(['pasien.user', 'rs'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('rs.pasien-nifas.index', compact('pasienNifas'));
    }

    /**
     * Show form create pasien nifas
     */
    public function create()
    {
        return view('rs.pasien-nifas.create');
    }

    /**
     * Simpan data pasien nifas baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pasien' => 'required|string|max:255',
            'nik' => 'required|digits:16',
            'no_telepon' => 'required|string|max:20',
            'provinsi' => 'required|string|max:100',
            'kota' => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'kelurahan' => 'required|string|max:100',
            'domisili' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $existingPasien = Pasien::where('nik', $validated['nik'])->first();

            if ($existingPasien) {
                $pasien = $existingPasien;
                
                $existingPasien->update([
                    'no_telepon' => $validated['no_telepon'],
                    'PProvinsi' => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah' => $validated['kelurahan'],
                ]);
                
            } else {
                $roleId = DB::table('roles')->where('nama_role', 'pasien')->first();
                
                if (!$roleId) {
                    throw new \Exception('Role "pasien" tidak ditemukan di database');
                }

                $emailExists = User::where('email', $validated['nik'] . '@pasien.delisa.id')->first();
                
                if ($emailExists) {
                    $email = $validated['nik'] . '.' . time() . '@pasien.delisa.id';
                } else {
                    $email = $validated['nik'] . '@pasien.delisa.id';
                }

                $user = User::create([
                    'name' => $validated['nama_pasien'],
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'role_id' => $roleId->id,
                ]);

                $pasienData = [
                    'user_id' => $user->id,
                    'nik' => $validated['nik'],
                    'no_telepon' => $validated['no_telepon'],
                    'PProvinsi' => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah' => $validated['kelurahan'],
                ];

                $pasien = Pasien::create($pasienData);
            }

            $rs_id = $this->getRsId();

            $existingNifas = PasienNifas::where('pasien_id', $pasien->id)
                                        ->where('rs_id', $rs_id)
                                        ->first();
            
            if ($existingNifas) {
                DB::commit();
                
                return redirect()
                    ->route('rs.pasien-nifas.show', $existingNifas->id)
                    ->with('info', 'Pasien sudah terdaftar. Silakan tambah data anak.');
            }

            $pasienNifasData = [
                'rs_id' => $rs_id,
                'pasien_id' => $pasien->id,
                'tanggal_mulai_nifas' => now(),
            ];

            $pasienNifas = PasienNifas::create($pasienNifasData);

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
        $user = auth()->user();
        
        if (isset($user->rumah_sakit_id) && !is_null($user->rumah_sakit_id)) {
            return $user->rumah_sakit_id;
        }
        
        if (isset($user->rs_id) && !is_null($user->rs_id)) {
            return $user->rs_id;
        }
        
        if (isset($user->puskesmas_id) && !is_null($user->puskesmas_id)) {
            return $user->puskesmas_id;
        }
        
        return 1;
    }

    /**
     * Display form tambah data anak
     */
    public function show($id)
    {
        $pasienNifas = PasienNifas::with(['pasien.user', 'rs', 'anakPasien'])
            ->findOrFail($id);
        
        return view('rs.pasien-nifas.show', compact('pasienNifas'));
    }

    /**
     * Store data anak pasien
     */
    public function storeAnakPasien(Request $request, $id)
    {
        $validated = $request->validate([
            'anak_ke' => 'required|integer|min:1',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nama_anak' => 'nullable|string|max:255',
            'usia_kehamilan_saat_lahir' => 'required|string',
            'berat_lahir_anak' => 'required|numeric|min:0',
            'panjang_lahir_anak' => 'required|numeric|min:0',
            'lingkar_kepala_anak' => 'required|numeric|min:0',
            'memiliki_buku_kia' => 'required|boolean',
            'buku_kia_bayi_kecil' => 'required|boolean',
            'imd' => 'required|boolean',
            'riwayat_penyakit' => 'nullable|array',
            'keterangan_masalah_lain' => 'nullable|string',
        ]);

        try {
            $pasienNifas = PasienNifas::findOrFail($id);

            AnakPasien::create([
                'nifas_id' => $pasienNifas->id,
                'anak_ke' => $validated['anak_ke'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nama_anak' => $validated['nama_anak'] ?? 'Anak ke-' . $validated['anak_ke'],
                'usia_kehamilan_saat_lahir' => $validated['usia_kehamilan_saat_lahir'],
                'berat_lahir_anak' => $validated['berat_lahir_anak'],
                'panjang_lahir_anak' => $validated['panjang_lahir_anak'],
                'lingkar_kepala_anak' => $validated['lingkar_kepala_anak'],
                'memiliki_buku_kia' => $validated['memiliki_buku_kia'],
                'buku_kia_bayi_kecil' => $validated['buku_kia_bayi_kecil'],
                'imd' => $validated['imd'],
                'riwayat_penyakit' => $validated['riwayat_penyakit'] ?? [],
                'keterangan_masalah_lain' => $validated['keterangan_masalah_lain'],
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
        $pasienNifas = PasienNifas::with([
            'pasien.user', 
            'rs', 
            'anakPasien'
        ])->findOrFail($id);
        
        // Ambil data anak pertama (atau bisa diubah sesuai kebutuhan)
        $anakPasien = $pasienNifas->anakPasien->first();
        
        return view('rs.pasien-nifas.detail', compact('pasienNifas', 'anakPasien'));
    }

    /**
     * Download PDF data pasien nifas
     */
    public function downloadPDF()
    {
        try {
            $pasienNifas = PasienNifas::with(['pasien.user', 'rs'])
                ->orderBy('created_at', 'desc')
                ->get();

            $pdf = Pdf::loadView('rs.pasien-nifas.pdf', compact('pasienNifas'));
            
            return $pdf->download('data-pasien-nifas-' . date('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal mengunduh PDF: ' . $e->getMessage());
        }
    }
}