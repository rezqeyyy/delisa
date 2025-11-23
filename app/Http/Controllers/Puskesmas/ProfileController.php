<?php

namespace App\Http\Controllers\Puskesmas; // ✅ SESUAI FOLDER

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends \App\Http\Controllers\Controller
{
    public function edit()
    {
        return view('puskesmas.profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
        {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . Auth::id(),
                'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'old_password' => 'nullable|required_with:password',
                'password' => 'nullable|min:8|required_with:old_password|confirmed',
            ]);

            /** @var \App\Models\User $user */
            $user = Auth::user();

            $user->name = $request->name;
            $user->email = $request->email;

            // Ganti password jika diisi
            if ($request->filled('password')) {
                if (!Hash::check($request->old_password, $user->password)) {
                    return back()->withErrors(['old_password' => 'Password lama tidak sesuai.']);
                }
                $user->password = Hash::make($request->password);
            }

            // Ganti foto
            if ($request->hasFile('photo')) {
                if ($user->photo) {
                    Storage::delete($user->photo);
                }
                $user->photo = $request->file('photo')->store('profile-photos', 'public');
            }

            $user->save();
            return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
        }

    public function destroyPhoto()
        {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($user->photo) {
                Storage::delete($user->photo);
                $user->photo = null;
                $user->save(); // ✅ Sekarang Intelephense tahu ini method dari model User
            }

            return redirect()->back()->with('success', 'Foto profil dihapus.');
        }
        }