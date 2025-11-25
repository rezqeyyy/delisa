<?php

namespace App\Http\Controllers\Auth;

// Controller dasar Laravel.
use App\Http\Controllers\Controller;

// Request berisi input user.
use Illuminate\Http\Request;

// Auth facade untuk login/logout.
use Illuminate\Support\Facades\Auth;

// Log facade untuk mencatat aktivitas login ke storage/logs/laravel.log
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * =============================================================
     *  GET /login
     *  Fungsi untuk menampilkan halaman login petugas (web umum)
     * =============================================================
     */
    public function create()
    {
        // Return view auth/login.blade.php
        return view('auth.login');
    }

    /**
     * =============================================================
     *  POST /login — proses login petugas
     * =============================================================
     */
    public function store(Request $request)
    {
        // Log awal percobaan login untuk debugging dan keamanan.
        Log::info('LOGIN_ATTEMPT_START', ['email' => $request->email]);

        /**
         * Validasi form login:
         * - email harus terisi dan dalam format email valid.
         * - password wajib terisi.
         */
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        /**
         * Auth::attempt():
         * - Mencoba login dengan kredensial.
         * - Parameter ke-2 → apakah user ingin remember me?
         * - Mengembalikan true jika berhasil.
         */
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {

            // Jika gagal → catat log peringatan.
            Log::warning('LOGIN_FAILED', ['email' => $request->email]);

            // Kembalikan ke halaman login dengan error.
            return back()
                ->withErrors(['email' => 'Kredensial salah.'])
                ->onlyInput('email'); // tetap isi email lama pada input
        }

        /**
         * Regenerate session ID:
         * - Menghindari session fixation attack.
         * - Wajib dilakukan setelah login sukses.
         */
        $request->session()->regenerate();

        // Ambil user yang sedang login
        $user = $request->user();

        /**
         * Catat log login sukses dengan detail:
         * - user_id
         * - email
         * - role (jika ada)
         * - session id baru
         */
        Log::info('LOGIN_ATTEMPT_OK', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'role'    => optional($user->role)->nama_role,
            'session' => $request->session()->getId(),
        ]);

        /**
         * Tentukan redirect tujuan berdasarkan role user.
         * Fungsi redirectPathByRole() berada di bawah.
         */
        $redirect = $this->redirectPathByRole($user);

        // Jika null → berarti role tidak dikenal.
        if ($redirect === null) {

            // Catat log kegagalan role tidak valid.
            Log::warning('LOGIN_FAILED_INVALID_ROLE', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'role'    => optional($user->role)->nama_role,
            ]);

            // Logout paksa karena role tidak valid.
            Auth::logout();

            // Kembalikan error.
            return back()
                ->withErrors([
                    'email' => 'Akun belum memiliki role yang benar. Hubungi admin untuk pengaturan role.',
                ])
                ->onlyInput('email');
        }

        // Jika semua aman → redirect ke dashboard sesuai role.
        return redirect()->to($redirect);
    }

    /**
     * =============================================================
     *  GET /login-pasien
     *  Tampilkan halaman login khusus pasien (bukan email/password)
     * =============================================================
     */
    public function createPasien()
    {
        return view('auth.login-pasien');
    }

    /**
     * =============================================================
     *  POST /login-pasien
     *  Login pasien menggunakan:
     *  - NIK (16 digit)
     *  - Nama lengkap
     * =============================================================
     */
    public function storePasien(Request $request)
    {
        // Validasi format input pasien
        $validated = $request->validate([
            'nik'          => ['required', 'string', 'size:16'], // wajib 16 digit
            'nama_lengkap' => ['required', 'string', 'min:3'],   // nama minimal 3 huruf
        ]);

        // Log usaha login pasien.
        Log::info('LOGIN_PASIEN_ATTEMPT_START', ['nik' => $validated['nik']]);

        /**
         * Cari pasien berdasarkan NIK
         * - with('user.role') memuat user + role sekaligus (eager loading)
         */
        $pasien = \App\Models\Pasien::with('user.role')
            ->where('nik', $validated['nik'])
            ->first();

        // Jika pasien tidak ditemukan atau user tidak ada
        if (!$pasien || !$pasien->user) {
            Log::warning('LOGIN_PASIEN_FAILED_NOT_FOUND', ['nik' => $validated['nik']]);
            return back()->withErrors(['nik' => 'NIK tidak ditemukan.'])->onlyInput('nik');
        }

        /**
         * Bandingkan nama input dengan nama asli di sistem.
         * - Ubah ke lowercase agar tidak case-sensitive.
         */
        $inputName = \Illuminate\Support\Str::lower(trim($validated['nama_lengkap']));
        $realName  = \Illuminate\Support\Str::lower(trim($pasien->user->name ?? ''));

        if ($inputName !== $realName) {
            Log::warning('LOGIN_PASIEN_FAILED_NAME_MISMATCH', [
                'nik'   => $validated['nik'],
                'input' => $inputName,
                'actual'=> $realName,
            ]);
            return back()->withErrors(['nama_lengkap' => 'Nama lengkap tidak sesuai.'])->onlyInput('nama_lengkap');
        }

        /**
         * Pastikan role user memang "pasien"
         */
        if (optional($pasien->user->role)->nama_role !== 'pasien') {
            Log::warning('LOGIN_PASIEN_FAILED_ROLE', [
                'nik'  => $validated['nik'],
                'role' => optional($pasien->user->role)->nama_role,
            ]);
            return back()->withErrors(['nik' => 'Akun tidak terdaftar sebagai pasien.'])->onlyInput('nik');
        }

        /**
         * LOGIN SUKSES untuk pasien
         */
        Auth::login($pasien->user);

        // Regenerasi session setelah login
        $request->session()->regenerate();

        Log::info('LOGIN_PASIEN_OK', [
            'user_id' => $pasien->user->id,
            'nik'     => $validated['nik'],
            'name'    => $pasien->user->name,
            'role'    => optional($pasien->user->role)->nama_role,
            'session' => $request->session()->getId(),
        ]);

        /**
         * Redirect otomatis:
         * - Jika role cocok → redirect by role
         * - Jika tidak → default ke dashboard pasien
         */
        $redirect = $this->redirectPathByRole($pasien->user) ?? route('pasien.dashboard');

        return redirect()->to($redirect);
    }

    /**
     * =============================================================
     *  POST /logout untuk petugas (dinkes/puskesmas/bidan/rs)
     * =============================================================
     */
    public function destroy(Request $request)
    {
        // Logout dari guard default
        Auth::guard()->logout();

        // Hapus semua session
        $request->session()->invalidate();

        // Buat token session baru agar aman
        $request->session()->regenerateToken();

        // Redirect ke halaman login petugas
        return redirect()->route('login');
    }

    /**
     * =============================================================
     *  POST /logout-pasien
     * =============================================================
     */
    public function destroyPasien(Request $request)
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke halaman login khusus pasien
        return redirect()->route('pasien.login');
    }

    /**
     * =============================================================
     *  Fungsi redirect berdasarkan role user
     * =============================================================
     * Menggunakan match expression (mirip switch case modern).
     * Jika role tidak ditemukan → return null.
     */
    protected function redirectPathByRole($user)
    {
        // Ambil role user secara aman (optional() menghindari error null)
        $role = optional($user->role)->nama_role;

        // Tentukan URL redirect berdasarkan role
        return match ($role) {
            'dinkes'            => route('dinkes.dashboard'),
            'puskesmas'         => route('puskesmas.dashboard'),
            'bidan'             => route('bidan.dashboard'),
            // Mendukung 2 variasi nama role untuk RS:
            'rs', 'rumah_sakit' => route('rs.dashboard'),
            'pasien'            => route('pasien.dashboard'),
            default             => null, // role tidak dikenal
        };
    }
}
