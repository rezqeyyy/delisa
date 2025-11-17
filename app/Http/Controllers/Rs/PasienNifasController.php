<?php

namespace App\Http\Controllers\Rs;

use App\Http\Controllers\Controller;
use App\Models\PasienNifas;
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
     * CATATAN: NIK boleh duplikat karena ini hanya menambah data nifas, bukan mendaftar pasien baru
     */
    public function store(Request $request)
    {
        // Validasi input - NIK TIDAK PERLU UNIQUE
        $validated = $request->validate([
            'nama_pasien' => 'required|string|max:255',
            'nik' => 'required|digits:16',
            'no_telepon' => 'required|string|max:20',
            'provinsi' => 'required|string|max:100',
            'kota' => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'kelurahan' => 'required|string|max:100',
            'domisili' => 'required|string',
        ], [
            'nama_pasien.required' => 'Nama pasien harus diisi',
            'nik.required' => 'NIK harus diisi',
            'nik.digits' => 'NIK harus 16 digit',
            'no_telepon.required' => 'Nomor telepon harus diisi',
            'provinsi.required' => 'Provinsi harus diisi',
            'kota.required' => 'Kota/Kabupaten harus diisi',
            'kecamatan.required' => 'Kecamatan harus diisi',
            'kelurahan.required' => 'Kelurahan harus diisi',
            'domisili.required' => 'Domisili harus diisi',
        ]);

        try {
            DB::beginTransaction();

            Log::info('=== START CREATE PASIEN NIFAS ===');
            Log::info('Validated Data:', $validated);
            Log::info('Auth User ID:', [auth()->id()]);

            // ✅ CEK APAKAH PASIEN SUDAH ADA BERDASARKAN NIK
            $existingPasien = Pasien::where('nik', $validated['nik'])->first();

            if ($existingPasien) {
                // ✅ PASIEN SUDAH ADA - Langsung daftarkan ke pasien_nifas_rs
                Log::info('Pasien already exists, using existing pasien_id:', ['id' => $existingPasien->id]);
                
                $pasien = $existingPasien;
                
                // Update data pasien jika perlu (opsional)
                $existingPasien->update([
                    'no_telepon' => $validated['no_telepon'],
                    'PProvinsi' => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah' => $validated['kelurahan'],
                ]);
                
                Log::info('Pasien data updated');
                
            } else {
                // ✅ PASIEN BELUM ADA - Buat user dan pasien baru
                Log::info('Creating new pasien');

                // 1. Buat user baru
                $roleId = DB::table('roles')->where('nama_role', 'pasien')->first();
                
                if (!$roleId) {
                    throw new \Exception('Role "pasien" tidak ditemukan di database');
                }

                // Cek apakah email sudah ada
                $emailExists = User::where('email', $validated['nik'] . '@pasien.delisa.id')->first();
                
                if ($emailExists) {
                    // Jika email sudah ada, tambahkan timestamp
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

                Log::info('User Created:', ['id' => $user->id, 'email' => $user->email]);

                // 2. Buat data pasien
                $pasienData = [
                    'user_id' => $user->id,
                    'nik' => $validated['nik'],
                    'no_telepon' => $validated['no_telepon'],
                    'PProvinsi' => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah' => $validated['kelurahan'],
                ];

                Log::info('Creating Pasien with data:', $pasienData);

                $pasien = Pasien::create($pasienData);

                Log::info('Pasien Created:', ['id' => $pasien->id, 'nik' => $pasien->nik]);
            }

            // ✅ DETEKSI rs_id OTOMATIS
            $rs_id = $this->getRsId();
            
            Log::info('Using RS ID:', ['rs_id' => $rs_id]);

            // ✅ CEK APAKAH SUDAH TERDAFTAR DI PASIEN NIFAS RS INI
            $existingNifas = PasienNifas::where('pasien_id', $pasien->id)
                                        ->where('rs_id', $rs_id)
                                        ->first();
            
            if ($existingNifas) {
                DB::commit();
                
                return redirect()
                    ->route('rs.pasien-nifas.index')
                    ->with('info', 'Pasien sudah terdaftar sebagai pasien nifas di rumah sakit ini.');
            }

            // 3. Daftarkan sebagai pasien nifas
            $pasienNifasData = [
                'rs_id' => $rs_id,
                'pasien_id' => $pasien->id,
                'tanggal_mulai_nifas' => now(),
            ];

            Log::info('Creating Pasien Nifas with data:', $pasienNifasData);

            $pasienNifas = PasienNifas::create($pasienNifasData);

            Log::info('Pasien Nifas Created:', ['id' => $pasienNifas->id]);
            Log::info('=== SUCCESS CREATE PASIEN NIFAS ===');

            DB::commit();

            return redirect()
                ->route('rs.pasien-nifas.index')
                ->with('success', 'Data pasien nifas berhasil ditambahkan!');

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
            Log::error('=== DATABASE ERROR ===');
            Log::error('SQL Error: ' . $e->getMessage());
            Log::error('Error Code: ' . $e->getCode());
            
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan database: ' . $e->getMessage());
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('=== ERROR CREATE PASIEN NIFAS ===');
            Log::error('Message: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile());
            Log::error('Line: ' . $e->getLine());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get RS ID from authenticated user
     * 
     * @return int
     */
    private function getRsId()
    {
        $user = auth()->user();
        
        // Cek berbagai kemungkinan field rs_id
        if (isset($user->rumah_sakit_id) && !is_null($user->rumah_sakit_id)) {
            return $user->rumah_sakit_id;
        }
        
        if (isset($user->rs_id) && !is_null($user->rs_id)) {
            return $user->rs_id;
        }
        
        if (isset($user->puskesmas_id) && !is_null($user->puskesmas_id)) {
            return $user->puskesmas_id;
        }
        
        // Ultimate fallback
        Log::warning('RS ID not found in user attributes, using default: 1');
        return 1;
    }

    /**
     * Display detail pasien nifas
     */
    public function show($id)
    {
        $pasienNifas = PasienNifas::with(['pasien.user', 'rs'])->findOrFail($id);
        
        return view('rs.pasien-nifas.show', compact('pasienNifas'));
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