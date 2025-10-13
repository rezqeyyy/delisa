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

        return redirect()->to($this->redirectPathByRole($user));
    }

    // POST /logout
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
