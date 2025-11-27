<?php

namespace App\Http\Controllers\Dinkes;

// Controller dasar Laravel
use App\Http\Controllers\Controller;

// Request → untuk menangkap input form
use Illuminate\Http\Request;

// Auth → untuk mengambil user yang sedang login
use Illuminate\Support\Facades\Auth;

// Hash → untuk verifikasi & hashing password
use Illuminate\Support\Facades\Hash;

// Storage → untuk upload & delete file foto profil
use Illuminate\Support\Facades\Storage;

// Eloquent Model User
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * ===========================================================
     *  PAGE: Edit Profile Dinkes
     *  GET /dinkes/profile/edit
     * ===========================================================
     */
    public function edit()
    {
        // Ambil user yang sedang login melalui Auth facade
        $user = Auth::user();

        // Kirim data user ke view profile EDIT
        return view('dinkes.profile.edit', compact('user'));
    }

    /**
     * ===========================================================
     *  ACTION: Update Profile (nama, foto, password)
     *  POST /dinkes/profile/update
     * ===========================================================
     */
    public function update(Request $request)
    {
        // Ambil record User dari database berdasarkan ID user yang login
        $user = User::find(Auth::id());

        /**
         * Validasi input form:
         * - name wajib string max 255
         * - photo → file opsional, tipe file tertentu termasuk SVG
         * - old_password → opsional
         * - password → opsional, minimal 8 karakter
         */
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],

            // NOTE:
            // "file" + "mimetypes" digunakan agar SVG lolos
            // (karena SVG sering tidak lolos rule image|mimes)
            'photo'        => [
                'nullable',
                'file',
                'mimetypes:image/svg+xml,image/png,image/jpeg,image/webp,image/avif'
            ],

            'old_password' => ['nullable', 'string'],
            'password'     => ['nullable', 'string', 'min:8'],
        ]);

        // Flags apakah password lama & baru diisi user
        $oldFilled = filled($request->old_password);
        $newFilled = filled($request->password);

        /**
         * ============================
         * VALIDASI LOGIKA PASSWORD
         * ============================
         *
         * 1. Jika old_password diisi, password baru HARUS diisi.
         * 2. Jika password baru diisi, old_password HARUS diisi.
         * 3. Jika keduanya diisi → verify old password harus benar.
         */

        if ($oldFilled && !$newFilled) {
            return back()
                ->withErrors(['password' => 'Isi password baru jika kamu mengisi password lama.'])
                ->withInput();
        }

        if ($newFilled && !$oldFilled) {
            return back()
                ->withErrors(['old_password' => 'Isi password lama untuk verifikasi sebelum mengganti password.'])
                ->withInput();
        }

        if ($oldFilled && $newFilled) {
            // Cek kecocokan password lama
            if (! Hash::check($request->old_password, $user->password)) {
                return back()
                    ->withErrors(['old_password' => 'Password lama tidak sesuai.'])
                    ->withInput();
            }

            // Jika cocok → hash password baru lalu simpan
            $user->password = Hash::make($request->password);
        }

        /**
         * ============================
         * UPDATE NAMA USER
         * ============================
         */
        $user->name = $request->name;

        /**
         * ============================
         * UPLOAD FOTO PROFIL
         * ============================
         * - Mendukung SVG & format raster
         * - Tidak dilakukan manipulasi gambar (resize dsb)
         * - Disimpan dalam folder:
         *       /storage/app/public/photos/users/{user->id}/avatar.svg|png|jpg
         */
        if ($request->hasFile('photo')) {

            // Jika user sudah punya foto & file masih ada → hapus dulu
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            // Folder khusus untuk user (agar rapi)
            $dir = "photos/users/{$user->id}";
            Storage::disk('public')->makeDirectory($dir);

            // Ekstensi asli file (svg/png/jpeg/webp/avif)
            $ext = $request->file('photo')->getClientOriginalExtension();

            // Nama file standar
            $name = 'avatar.' . strtolower($ext ?: 'bin');

            // Simpan file ke folder user
            $path = $request->file('photo')->storeAs($dir, $name, 'public');

            // Simpan path relatif ke kolom user.photo
            $user->photo = $path;
        }

        // Simpan semua perubahan user ke database
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * ===========================================================
     *  ACTION: Hapus Foto Profil
     *  POST /dinkes/profile/delete-photo
     * ===========================================================
     */
    public function destroyPhoto(Request $request)
    {
        // Ambil data user yang login
        $user = User::find(Auth::id());

        // Jika user punya foto & file ada di storage → hapus
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        // Set kolom photo ke NULL → kembali ke avatar default
        $user->photo = null;
        $user->save();

        return back()->with('success', 'Foto profil dihapus. Menggunakan avatar default.');
    }
}
