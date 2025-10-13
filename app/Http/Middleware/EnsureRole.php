<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class EnsureRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user();
        Log::info('ENSUREROLE_CHECK', [
            'url'        => $request->path(),
            'roles_need' => $roles,
            'auth'       => Auth::check(),
            'user'       => $user?->email,
            'user_role'  => optional($user?->role)->nama_role,
            'session_id' => $request->session()->getId(),
        ]);

        if (!$user) {
            Log::warning('ENSUREROLE_NO_USER');
            return redirect()->route('login');
        }

        $namaRole = optional($user->role)->nama_role;
        if (!in_array($namaRole, $roles, true)) {
            Log::warning('ENSUREROLE_DENY', ['expected' => $roles, 'got' => $namaRole]);
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
