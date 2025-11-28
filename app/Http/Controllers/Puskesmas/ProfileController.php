<?php

namespace App\Http\Controllers\Puskesmas; // ✅ SESUAI FOLDER

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * Controller untuk menangani pengelolaan profil pengguna.
 * Memungkinkan pengguna untuk mengedit, memperbarui profil, dan menghapus foto profil.
 */
class ProfileController extends \App\Http\Controllers\Controller
{
    /**
     * Menampilkan form edit profil pengguna.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit()
    {
        // Catatan: Mengambil data pengguna yang sedang login menggunakan Auth::user()
        // dan mengirimkannya ke view untuk ditampilkan di form edit profil.
        return view('puskesmas.profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Memperbarui data profil pengguna berdasarkan input dari request.
     *
     * @param Request $request Data yang dikirimkan dari form edit profil.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Catatan: Validasi input dari form.
        // - Nama wajib, string, maksimal 255 karakter.
        // - Email wajib, format valid, dan unik di tabel users kecuali untuk user saat ini.
        // - Foto opsional, harus berupa file gambar (jpg, jpeg, png) dengan ukuran maksimal 2MB.
        // - old_password wajib jika password baru diisi.
        // - password baru harus minimal 8 karakter, wajib jika old_password diisi, dan harus konfirmasi.
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'old_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|required_with:old_password|confirmed',
        ]);

        /** @var \App\Models\User $user */
        // Catatan: Mengambil instance model User dari pengguna yang sedang login.
        $user = Auth::user();

        // Catatan: Memperbarui nama dan email pengguna.
        $user->name = $request->name;
        $user->email = $request->email;

        // Catatan: Memeriksa apakah password baru diisi.
        // Jika ya, maka password lama harus dicek kecocokannya.
        if ($request->filled('password')) {
            // Catatan: Memeriksa apakah password lama yang dimasukkan cocok dengan yang ada di database.
            if (!Hash::check($request->old_password, $user->password)) {
                // Catatan: Jika tidak cocok, kembali ke halaman sebelumnya dengan error.
                return back()->withErrors(['old_password' => 'Password lama tidak sesuai.']);
            }
            // Catatan: Jika cocok, maka password lama benar dan password baru di-hash dan disimpan.
            $user->password = Hash::make($request->password);
        }

        // Catatan: Memeriksa apakah ada file foto yang diunggah.
        if ($request->hasFile('photo')) {
            // Catatan: Jika pengguna sebelumnya sudah memiliki foto profil, hapus foto lama dari storage.
            if ($user->photo) {
                Storage::delete($user->photo);
            }
            // Catatan: Simpan foto baru ke storage dan simpan path-nya ke kolom 'photo' di database.
            $user->photo = $request->file('photo')->store('profile-photos', 'public');
        }

        // Catatan: Simpan semua perubahan ke database.
        $user->save();

        // Catatan: Redirect kembali ke halaman sebelumnya dengan pesan sukses.
        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Menghapus foto profil pengguna yang sedang login.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyPhoto()
    {
        /** @var \App\Models\User $user */
        // Catatan: Mengambil instance model User dari pengguna yang sedang login.
        $user = Auth::user();

        // Catatan: Memeriksa apakah pengguna memiliki foto profil.
        if ($user->photo) {
            // Catatan: Hapus foto lama dari storage.
            Storage::delete($user->photo);
            // Catatan: Kosongkan kolom 'photo' di database.
            $user->photo = null;
            // Catatan: Simpan perubahan ke database.
            $user->save(); // ✅ Sekarang Intelephense tahu ini method dari model User
        }

        // Catatan: Redirect kembali ke halaman sebelumnya dengan pesan sukses.
        return redirect()->back()->with('success', 'Foto profil dihapus.');
    }
}