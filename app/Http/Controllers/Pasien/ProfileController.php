<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Pasien;

/**
 * Controller edit profil untuk user dengan role "pasien".
 * - Menampilkan form edit profil
 * - Mengupdate data profil (users & pasiens)
 * - Mengelola upload / hapus foto profil
 */
class ProfileController extends Controller
{
    /**
     * Tampilkan halaman edit profil pasien.
     * View: resources/views/pasien/profile.blade.php
     * Data yang dikirim: $user (users), $pasien (pasiens)
     */
    public function edit()
    {
        $user   = Auth::user();
        $pasien = $user?->pasien;
        return view('pasien.profile', compact('user', 'pasien'));
    }

    /**
     * Update data profil pasien.
     * - users: name, address, phone, password (opsional), photo (opsional)
     * - pasiens: nik
     * Aturan ganti password:
     *   - Wajib isi "old_password" dan "password" bersamaan.
     *   - "old_password" harus cocok dengan password tersimpan.
     * Upload foto:
     *   - Disimpan di storage disk "public" path: photos/users/{user_id}/avatar.{ext}
     *   - MIME type mendukung SVG dan gambar raster umum.
     */
    public function update(Request $request)
    {
        $user = \App\Models\User::find(Auth::id());
        $pasien = Pasien::firstOrCreate(['user_id' => $user->id]);

        // Validasi input form
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'nik'          => ['nullable', 'string', 'max:32'],
            'address'      => ['nullable', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:32'],
            'photo'        => [
                'nullable',
                'file',
                'mimetypes:image/svg+xml,image/png,image/jpeg,image/webp,image/avif'
            ],
            'old_password' => ['nullable', 'string'],
            'password'     => ['nullable', 'string', 'min:8'],
        ]);

        // Cek pengisian password lama & baru
        $oldFilled = filled($request->old_password);
        $newFilled = filled($request->password);

        // Kedua field password harus diisi bersama; tangani kasus tidak lengkap
        if ($oldFilled && !$newFilled) {
            return back()->withErrors(['password' => 'Isi password baru jika kamu mengisi password lama.'])->withInput();
        }
        if ($newFilled && !$oldFilled) {
            return back()->withErrors(['old_password' => 'Isi password lama untuk verifikasi sebelum mengganti password.'])->withInput();
        }
        // Verifikasi password lama dan set password baru
        if ($oldFilled && $newFilled) {
            if (! Hash::check($request->old_password, $user->password)) {
                return back()->withErrors(['old_password' => 'Password lama tidak sesuai.'])->withInput();
            }
            $user->password = Hash::make($request->password);
        }

        // Simpan field pada tabel users
        $user->name    = $request->name;
        $user->address = $request->address;
        $user->phone   = $request->phone;

        // Simpan NIK pada tabel pasiens
        $pasien->nik = $request->nik;

        // Upload foto profil (opsional) — overwrite jika sudah ada
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            // Simpan foto baru dengan penamaan konsisten
            $dir  = "photos/users/{$user->id}";
            Storage::disk('public')->makeDirectory($dir);
            $ext  = $request->file('photo')->getClientOriginalExtension();
            $name = 'avatar.' . strtolower($ext ?: 'bin');
            $path = $request->file('photo')->storeAs($dir, $name, 'public');
            $user->photo = $path;
        }

        $user->save();
        $pasien->save();

        return back()->with('success', 'Profil pasien berhasil diperbarui.');
    }

    /**
     * Hapus foto profil pasien.
     * - Menghapus file di storage/public jika ada
     * - Mengosongkan kolom photo di tabel users
     */
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