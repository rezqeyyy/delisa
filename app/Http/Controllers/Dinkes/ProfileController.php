<?php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('dinkes.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = \App\Models\User::find(Auth::id());

        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            // pakai "file" + mimetypes agar SVG lolos; max dalam KB (512 KB)
            'photo'        => [
                'nullable',
                'file',
                'mimetypes:image/svg+xml,image/png,image/jpeg,image/webp,image/avif'
            ],
            'old_password' => ['nullable', 'string'],
            'password'     => ['nullable', 'string', 'min:8'],
        ]);

        $oldFilled = filled($request->old_password);
        $newFilled = filled($request->password);

        if ($oldFilled && !$newFilled) {
            return back()->withErrors(['password' => 'Isi password baru jika kamu mengisi password lama.'])->withInput();
        }
        if ($newFilled && !$oldFilled) {
            return back()->withErrors(['old_password' => 'Isi password lama untuk verifikasi sebelum mengganti password.'])->withInput();
        }
        if ($oldFilled && $newFilled) {
            if (! Hash::check($request->old_password, $user->password)) {
                return back()->withErrors(['old_password' => 'Password lama tidak sesuai.'])->withInput();
            }
            $user->password = Hash::make($request->password);
        }

        // Update nama
        $user->name = $request->name;

        // Upload file (SVG atau raster) — tanpa manipulasi
        if ($request->hasFile('photo')) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            // simpan di folder per-user agar rapi
            $dir  = "photos/users/{$user->id}";
            Storage::disk('public')->makeDirectory($dir);
            $ext  = $request->file('photo')->getClientOriginalExtension(); // contoh: svg, png
            $name = 'avatar.' . strtolower($ext ?: 'bin');
            $path = $request->file('photo')->storeAs($dir, $name, 'public');

            $user->photo = $path;
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function destroyPhoto(Request $request)
    {
        $user = \App\Models\User::find(Auth::id());

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->photo = null;
        $user->save();

        return back()->with('success', 'Foto profil dihapus. Menggunakan avatar default.');
    }
}
