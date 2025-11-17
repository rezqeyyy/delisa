<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Bidan;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman edit profil bidan.
     */
    public function edit(Request $request)
    {
        /** @var User $user */
        $user  = Auth::user();

        /** @var Bidan|null $bidan */
        $bidan = $user->bidan;

        if (! $bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }

        return view('bidan.profile.edit', compact('user', 'bidan'));
    }

    /**
     * Update data profil bidan + user yang terkait.
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user  = Auth::user();

        /** @var Bidan|null $bidan */
        $bidan = $user->bidan;

        if (! $bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }

        // Validasi input
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['nullable', 'email', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:255'],
            'address'             => ['nullable', 'string', 'max:255'],
            'nomor_izin_praktek'  => ['required', 'string', 'max:255'],
            'password'            => ['nullable', 'string', 'min:8', 'confirmed'],
            'photo'               => ['nullable', 'image', 'max:2048'],
        ]);

        // ===== Update data user =====
        $user->name    = $validated['name'];
        $user->email   = $validated['email'] ?? null;
        $user->phone   = $validated['phone'] ?? null;
        $user->address = $validated['address'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $path = $request->file('photo')->store('profile-photos', 'public');
            $user->photo = $path;
        }

        $user->save(); // sekarang Intelephense tahu ini User

        // ===== Update data bidan =====
        $bidan->nomor_izin_praktek = $validated['nomor_izin_praktek'];
        $bidan->save(); // dan ini Bidan

        return redirect()
            ->route('bidan.profile.edit')
            ->with('status', 'Profil berhasil diperbarui.');
    }

    /**
     * Hapus foto profil bidan.
     */
    public function destroyPhoto(Request $request)
    {
        /** @var User $user */
        $user  = Auth::user();

        /** @var Bidan|null $bidan */
        $bidan = $user->bidan;

        if (! $bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->photo = null;
            $user->save();
        }

        return redirect()
            ->route('bidan.profile.edit')
            ->with('status', 'Foto profil berhasil dihapus.');
    }
}
