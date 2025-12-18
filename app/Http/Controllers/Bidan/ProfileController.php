<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Bidan;
use App\Models\User;
use Illuminate\Support\Facades\DB;


/*
|--------------------------------------------------------------------------
| PROFILE CONTROLLER
|--------------------------------------------------------------------------
| Fungsi: Mengelola profil bidan
| Fitur: Edit profil, update data, ganti password, upload/hapus foto
|--------------------------------------------------------------------------
*/

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | METHOD: edit()
    |--------------------------------------------------------------------------
    | Fungsi: Menampilkan halaman form edit profil bidan
    | Return: View 'bidan.profile.edit' dengan data user & bidan
    |--------------------------------------------------------------------------
    */
    public function edit(Request $request)
    {
        /** @var User $user */
        // Anotasi untuk IDE autocompletion (Intelephense)
        $user  = Auth::user(); // Ambil user yang sedang login

        /** @var Bidan|null $bidan */
        // Anotasi untuk IDE (tipe data Bidan atau null)
        $bidan = $user->bidan; // Ambil data bidan dari relasi user->bidan

        // Validasi apakah user memiliki data bidan
        if (! $bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }

        // Kirim data user & bidan ke view
        // Ambil informasi puskesmas binaan (nama + kecamatan)
        // 1) Ambil dulu record puskesmas yang direferensikan oleh bidan.puskesmas_id
        // Ambil dulu record puskesmas yg direferensikan oleh bidan.puskesmas_id
        $puskesmasRef = DB::table('puskesmas')
            ->where('id', $bidan->puskesmas_id)
            ->select('id', 'nama_puskesmas', 'kecamatan', 'is_mandiri')
            ->first();

        // Default: kalau bukan mandiri, binaannya = puskesmasRef itu sendiri
        $puskesmasBinaan = $puskesmasRef
            ? (object) [
                'nama_puskesmas' => $puskesmasRef->nama_puskesmas,
                'kecamatan'      => $puskesmasRef->kecamatan,
            ]
            : null;

        // Kalau puskesmasRef adalah klinik mandiri, cari puskesmas binaan (non-mandiri) berdasarkan kecamatan yang sama
        if ($puskesmasRef && $puskesmasRef->is_mandiri === true) {
            $puskesmasBinaan = DB::table('puskesmas')
                ->where('kecamatan', $puskesmasRef->kecamatan)
                ->where(function ($q) {
                    $q->whereNull('is_mandiri')
                        ->orWhere('is_mandiri', false);
                })
                ->select('nama_puskesmas', 'kecamatan')
                ->orderBy('id') // biar deterministik
                ->first()
                ?: $puskesmasBinaan; // fallback kalau tidak ketemu
        }
        // Kirim data user, bidan, dan puskesmas binaan ke view
        return view('bidan.profile.edit', compact('user', 'bidan', 'puskesmasBinaan'));
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: update()
    |--------------------------------------------------------------------------
    | Fungsi: Update data profil bidan (user + bidan)
    | Parameter: $request (data dari form)
    | Return: Redirect ke halaman edit dengan pesan sukses
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        /** @var User $user */
        $user  = Auth::user(); // Ambil user yang login

        /** @var Bidan|null $bidan */
        $bidan = $user->bidan; // Ambil data bidan

        // Validasi akses bidan
        if (! $bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }

        // ========================================================
        // 1. VALIDASI INPUT FORM
        // ========================================================
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],  // Nama wajib, string, max 255
            'email'               => ['nullable', 'email', 'max:255'],   // Email opsional, format email
            'phone'               => ['nullable', 'string', 'max:255'],  // No telp opsional
            'address'             => ['nullable', 'string', 'max:255'],  // Alamat opsional
            'nomor_izin_praktek'  => ['required', 'string', 'max:255'],  // Nomor izin wajib
            'password'            => ['nullable', 'string', 'min:8', 'confirmed'], // Password opsional, min 8 karakter, harus sama dengan password_confirmation
            'photo'               => ['nullable', 'image', 'max:2048'],  // Foto opsional, harus gambar, max 2MB
        ]);

        // ========================================================
        // 2. UPDATE DATA USER
        // ========================================================
        $user->name    = $validated['name'];        // Update nama
        $user->email   = $validated['email'] ?? null;   // Update email, null jika tidak diisi
        $user->phone   = $validated['phone'] ?? null;   // Update phone, null jika tidak diisi
        $user->address = $validated['address'] ?? null; // Update alamat, null jika tidak diisi

        // Update password jika diisi
        if (! empty($validated['password'])) {
            // Hash::make(): hash password dengan bcrypt
            $user->password = Hash::make($validated['password']);
        }

        // ========================================================
        // 3. UPDATE FOTO PROFIL (jika ada upload baru)
        // ========================================================
        if ($request->hasFile('photo')) { // Cek apakah ada file foto di-upload
            // Hapus foto lama jika ada
            if ($user->photo) {
                // Storage::disk('public'): akses folder storage/app/public
                // delete($path): hapus file di path tersebut
                Storage::disk('public')->delete($user->photo);
            }

            // Simpan foto baru
            // store('folder', 'disk'): simpan file ke folder & disk tertentu
            // Return: path file (contoh: profile-photos/abc123.jpg)
            $path = $request->file('photo')->store('profile-photos', 'public');

            // Set path foto ke user
            $user->photo = $path;
        }

        // Simpan perubahan user ke database
        $user->save(); // Anotasi @var membantu IDE tahu ini Model User

        // ========================================================
        // 4. UPDATE DATA BIDAN
        // ========================================================
        // Update nomor izin praktek
        $bidan->nomor_izin_praktek = $validated['nomor_izin_praktek'];

        // Simpan perubahan bidan ke database
        $bidan->save(); // Anotasi @var membantu IDE tahu ini Model Bidan

        // ========================================================
        // 5. REDIRECT DENGAN FLASH MESSAGE
        // ========================================================
        return redirect()
            ->route('bidan.profile.edit') // Redirect ke halaman edit profil
            ->with('status', 'Profil berhasil diperbarui.'); // Flash message sukses
    }

    /*
    |--------------------------------------------------------------------------
    | METHOD: destroyPhoto()
    |--------------------------------------------------------------------------
    | Fungsi: Menghapus foto profil bidan
    | Return: Redirect ke halaman edit dengan pesan sukses
    |--------------------------------------------------------------------------
    */
    public function destroyPhoto(Request $request)
    {
        /** @var User $user */
        $user  = Auth::user(); // Ambil user yang login

        /** @var Bidan|null $bidan */
        $bidan = $user->bidan; // Ambil data bidan

        // Validasi akses bidan
        if (! $bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }

        // Hapus foto jika ada
        if ($user->photo) {
            // Hapus file foto dari storage
            Storage::disk('public')->delete($user->photo);

            // Set kolom photo jadi null
            $user->photo = null;

            // Simpan perubahan
            $user->save();
        }

        // Redirect dengan flash message
        return redirect()
            ->route('bidan.profile.edit')
            ->with('status', 'Foto profil berhasil dihapus.');
    }
}

/*
|--------------------------------------------------------------------------
| PENJELASAN FUNGSI-FUNGSI:
|--------------------------------------------------------------------------
|
| 1. @var Type $variable
|    - PHPDoc anotasi untuk IDE
|    - Memberitahu IDE tipe data variable
|    - Membantu autocompletion & deteksi error
|    - Tidak mempengaruhi eksekusi code
|
| 2. $request->validate([...])
|    - Validasi input dari form
|    - Jika gagal: otomatis redirect back dengan error
|    - Jika sukses: return array data yang sudah divalidasi
|    - Rules: required, nullable, string, email, min, max, confirmed, image
|
| 3. Hash::make($password)
|    - Hash password dengan algoritma bcrypt
|    - Hasil: string panjang (60 karakter)
|    - Tidak bisa di-decrypt (one-way hashing)
|    - Laravel otomatis verify saat login dengan Hash::check()
|
| 4. $request->hasFile('nama_field')
|    - Cek apakah ada file di-upload di field tersebut
|    - Return: true jika ada file, false jika tidak
|    - Contoh: $request->hasFile('photo')
|
| 5. Storage::disk('disk_name')
|    - Akses disk storage tertentu
|    - 'public': storage/app/public (bisa diakses publik)
|    - 'local': storage/app (private)
|    - Return: Filesystem object
|
| 6. Storage::delete($path)
|    - Hapus file di path tertentu
|    - $path: path relatif dari disk (contoh: profile-photos/abc.jpg)
|    - Return: true jika berhasil, false jika gagal
|
| 7. $file->store('folder', 'disk')
|    - Simpan uploaded file ke storage
|    - 'folder': subfolder dalam disk
|    - 'disk': disk tujuan (public, local, s3, dll)
|    - Return: path file (dengan nama random otomatis)
|
| 8. $model->save()
|    - Simpan perubahan model ke database
|    - Untuk model baru: INSERT
|    - Untuk model existing: UPDATE
|    - Return: true jika berhasil
|
| 9. ?? (Null Coalescing Operator)
|    - Ambil nilai kiri jika tidak null, jika null ambil nilai kanan
|    - Contoh: $x ?? 'default' -> return $x jika $x tidak null, return 'default' jika null
|    - Lebih singkat dari: isset($x) ? $x : 'default'
|
| 10. ! (NOT Operator)
|     - Negasi boolean
|     - !true = false
|     - !false = true
|     - Contoh: if (!$bidan) -> jika $bidan false/null
|
| 11. empty($var)
|     - Cek apakah variable kosong
|     - Empty: null, '', 0, [], false
|     - Return: true jika kosong, false jika tidak
|
| 12. compact('var1', 'var2', ...)
|     - Buat array asosiatif dari variable
|     - compact('user', 'bidan') = ['user' => $user, 'bidan' => $bidan]
|     - Untuk passing data ke view
|
| 13. redirect()->route('nama_route')
|     - Redirect ke route tertentu
|     - route(): ambil URL dari nama route
|     - Return: RedirectResponse
|
| 14. ->with('key', 'value')
|     - Simpan flash data ke session
|     - Data hanya tersedia untuk 1 request berikutnya
|     - Diakses di view: session('key') atau @if(session('key'))
|
| 15. 'confirmed' validation rule
|     - Validasi field harus sama dengan field_confirmation
|     - Contoh: 'password' => 'confirmed'
|     - Laravel otomatis cari field 'password_confirmation'
|     - Harus match, jika tidak validasi gagal
|
| 16. 'image' validation rule
|     - Validasi file harus gambar
|     - Format: jpeg, png, bmp, gif, svg, webp
|     - Cek MIME type file
|
| 17. 'max:2048' untuk file
|     - Validasi ukuran file maksimal
|     - Satuan: kilobytes (KB)
|     - max:2048 = maksimal 2MB
|
|--------------------------------------------------------------------------
| TIPS UPLOAD FILE:
|--------------------------------------------------------------------------
|
| 1. Pastikan form punya enctype="multipart/form-data"
| 2. Pastikan config/filesystems.php sudah setup disk 'public'
| 3. Jalankan: php artisan storage:link untuk symlink storage ke public
| 4. Akses file: asset('storage/' . $user->photo)
| 5. Selalu hapus file lama sebelum upload file baru (hindari sampah)
|
|--------------------------------------------------------------------------
*/