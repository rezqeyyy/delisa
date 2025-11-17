<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    // GET /login
    public function create()
    {
        // tampilkan halaman login
        return view('auth.login');
    }

    // POST /login
    public function store(Request $request)
    {
        Log::info('LOGIN_ATTEMPT_START', ['email' => $request->email]);

        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            Log::warning('LOGIN_FAILED', ['email' => $request->email]);
            return back()
                ->withErrors(['email' => 'Kredensial salah.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();

        Log::info('LOGIN_ATTEMPT_OK', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'role'    => optional($user->role)->nama_role,
            'session' => $request->session()->getId(),
        ]);

        // --- PENTING: gunakan hasil redirect sekali saja ---
        $redirect = $this->redirectPathByRole($user);

        // kalau redirect null -> role tidak dikenal
        if ($redirect === null) {
            Log::warning('LOGIN_FAILED_INVALID_ROLE', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'role'    => optional($user->role)->nama_role,
            ]);

            Auth::logout();

            return back()
                ->withErrors([
                    'email' => 'Akun belum memiliki role yang benar. Hubungi admin untuk pengaturan role.',
                ])
                ->onlyInput('email');
        }

        return redirect()->to($redirect);
    }

    // GET /login-pasien
    public function createPasien()
    {
        return view('auth.login-pasien');
    }

    // POST /login-pasien
    public function storePasien(Request $request)
    {
        // validasi NIK (16 digit) dan nama lengkap
        $validated = $request->validate([
            'nik'          => ['required', 'string', 'size:16'],
            'nama_lengkap' => ['required', 'string', 'min:3'],
        ]);

        Log::info('LOGIN_PASIEN_ATTEMPT_START', ['nik' => $validated['nik']]);

        $pasien = \App\Models\Pasien::with('user.role')
            ->where('nik', $validated['nik'])
            ->first();

        if (!$pasien || !$pasien->user) {
            Log::warning('LOGIN_PASIEN_FAILED_NOT_FOUND', ['nik' => $validated['nik']]);
            return back()->withErrors(['nik' => 'NIK tidak ditemukan.'])->onlyInput('nik');
        }

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

        if (optional($pasien->user->role)->nama_role !== 'pasien') {
            Log::warning('LOGIN_PASIEN_FAILED_ROLE', [
                'nik'  => $validated['nik'],
                'role' => optional($pasien->user->role)->nama_role,
            ]);
            return back()->withErrors(['nik' => 'Akun tidak terdaftar sebagai pasien.'])->onlyInput('nik');
        }

        Auth::login($pasien->user);
        $request->session()->regenerate();

        Log::info('LOGIN_PASIEN_OK', [
            'user_id' => $pasien->user->id,
            'nik'     => $validated['nik'],
            'name'    => $pasien->user->name,
            'role'    => optional($pasien->user->role)->nama_role,
            'session' => $request->session()->getId(),
        ]);

        $redirect = $this->redirectPathByRole($pasien->user) ?? route('pasien.dashboard');

        return redirect()->to($redirect);
    }

    // POST /logout petugas
    public function destroy(Request $request)
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // POST /logout pasien
    public function destroyPasien(Request $request)
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pasien.login');
    }

    /**
     * Tentukan redirect berdasarkan role user.
     * Kalau role tidak dikenal -> return null.
     */
    protected function redirectPathByRole($user)
    {
        $role = optional($user->role)->nama_role;

        return match ($role) {
            'dinkes'          => route('dinkes.dashboard'),
            'puskesmas'       => route('puskesmas.dashboard'),
            'bidan'           => route('bidan.dashboard'),
            // dukung 2 kemungkinan nama role untuk RS:
            'rs', 'rumah_sakit' => route('rs.dashboard'),
            'pasien'          => route('pasien.dashboard'),
            default           => null,
        };
    }
}
