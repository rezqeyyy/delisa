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
            return back()->withErrors(['email' => 'Kredensial salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();

        Log::info('LOGIN_ATTEMPT_OK', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'role'    => optional($user->role)->nama_role,
            'session' => $request->session()->getId(),
        ]);

                $redirect = $this->redirectPathByRole($user);
        if ($redirect === route('login')) {
            \Illuminate\Support\Facades\Auth::logout();
            return back()
                ->withErrors(['email' => 'Akun belum memiliki role yang benar. Hubungi admin untuk pengaturan role.'])
                ->onlyInput('email');
        }

        return redirect()->to($this->redirectPathByRole($user));
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

        // cari pasien berdasarkan NIK dan ambil user terkait
        // gunakan Eloquent agar mudah mengakses relasi
        \Illuminate\Support\Facades\Log::info('LOGIN_PASIEN_ATTEMPT_START', ['nik' => $validated['nik']]);

        $pasien = \App\Models\Pasien::with('user.role')
            ->where('nik', $validated['nik'])
            ->first();

        if (!$pasien || !$pasien->user) {
            \Illuminate\Support\Facades\Log::warning('LOGIN_PASIEN_FAILED_NOT_FOUND', ['nik' => $validated['nik']]);
            return back()->withErrors(['nik' => 'NIK tidak ditemukan.'])->onlyInput('nik');
        }

        // bandingkan nama lengkap (case-insensitive, trim)
        $inputName = \Illuminate\Support\Str::lower(trim($validated['nama_lengkap']));
        $realName  = \Illuminate\Support\Str::lower(trim($pasien->user->name ?? ''));

        if ($inputName !== $realName) {
            \Illuminate\Support\Facades\Log::warning('LOGIN_PASIEN_FAILED_NAME_MISMATCH', [
                'nik' => $validated['nik'], 'input' => $inputName, 'actual' => $realName,
            ]);
            return back()->withErrors(['nama_lengkap' => 'Nama lengkap tidak sesuai.'])->onlyInput('nama_lengkap');
        }

        // opsional: pastikan role user adalah pasien
        if (optional($pasien->user->role)->nama_role !== 'pasien') {
            \Illuminate\Support\Facades\Log::warning('LOGIN_PASIEN_FAILED_ROLE', [
                'nik' => $validated['nik'], 'role' => optional($pasien->user->role)->nama_role,
            ]);
            return back()->withErrors(['nik' => 'Akun tidak terdaftar sebagai pasien.'])->onlyInput('nik');
        }

        // login-kan user
        \Illuminate\Support\Facades\Auth::login($pasien->user);

        $request->session()->regenerate();

        \Illuminate\Support\Facades\Log::info('LOGIN_PASIEN_OK', [
            'user_id' => $pasien->user->id,
            'nik'     => $validated['nik'],
            'name'    => $pasien->user->name,
            'role'    => optional($pasien->user->role)->nama_role,
            'session' => $request->session()->getId(),
        ]);

        return redirect()->to($this->redirectPathByRole($pasien->user));
    }

    // POST /logout petugas
    public function destroy(Request $request)
    {
        // hapus auth
        Auth::guard()->logout();

        // invalidasi semua data session & regen CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // opsional: clear cookie session browser (biasanya cukup invalidate())
        // cookie()->queue(cookie()->forget(config('session.cookie')));

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

    protected function redirectPathByRole($user)
    {
        $role = optional($user->role)->nama_role;

        return match ($role) {
            'dinkes'      => route('dinkes.dashboard'),
            'puskesmas'   => route('puskesmas.dashboard'),
            'bidan'       => route('bidan.dashboard'),
            'rumah_sakit' => route('rs.dashboard'),
            'pasien'      => route('pasien.dashboard'),
            default       => route('login'),
        };
    }
}
