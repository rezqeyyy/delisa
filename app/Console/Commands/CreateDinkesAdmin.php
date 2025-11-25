<?php
/**
 * File ini berada di folder: app/Console/Commands
 * dan berfungsi sebagai perintah artisan khusus untuk membuat akun admin Dinkes.
 */

namespace App\Console\Commands;

// Mengimpor class dasar untuk membuat command Artisan.
use Illuminate\Console\Command;

// Class untuk melakukan hashing password secara aman (bcrypt).
use Illuminate\Support\Facades\Hash;

// Class helper untuk membuat string acak (digunakan untuk remember_token).
use Illuminate\Support\Str;

// DB facade untuk menjalankan query database secara low-level.
use Illuminate\Support\Facades\DB;

// Model User agar dapat membuat atau memperbarui user.
use App\Models\User;

class CreateDinkesAdmin extends Command
{
    /**
     * $signature adalah perintah yang dipanggil via terminal:
     * php artisan create:dinkes-admin
     */
    protected $signature = 'create:dinkes-admin';

    /**
     * Deskripsi command, muncul saat menjalankan php artisan list.
     */
    protected $description = 'Create or update the Dinkes master admin account securely';

    /**
     * Fungsi utama yang akan dijalankan ketika command dipanggil.
     */
    public function handle()
    {
        /**
         * =========================================
         * 1) Mengambil role 'dinkes' dari tabel roles
         * =========================================
         * - DB::table(...): query builder.
         * - where(...): mencari record dengan nama_role = 'dinkes'.
         * - value('id'): mengambil kolom 'id' saja, langsung sebagai integer.
         */
        $roleId = DB::table('roles')->where('nama_role', 'dinkes')->value('id');

        /**
         * Jika role belum ada, hentikan command.
         * $this->error(): menampilkan pesan error merah di console.
         */
        if (!$roleId) {
            $this->error('Role "dinkes" belum ada di tabel roles. Tambahkan dulu.');
            return 1; // return non-zero artinya gagal
        }

        /**
         * =========================================
         * 2) Input email admin
         * =========================================
         * $this->ask() => menampilkan pertanyaan ke terminal dan menerima input user.
         * Parameter ke-2 adalah default value jika user langsung tekan ENTER.
         */
        $email = $this->ask('Masukkan email admin Dinkes', 'admin@gmail.com');

        /**
         * Input nama lengkap admin.
         */
        $name  = $this->ask('Masukkan nama lengkap admin', 'Admin Dinkes Kota Depok');

        /**
         * Input password tetapi tidak ditampilkan saat mengetik.
         * $this->secret() => menyembunyikan input.
         */
        $pass  = $this->secret('Masukkan password awal (tidak akan terlihat)');

        /**
         * Validasi bahwa password tidak boleh kosong.
         */
        if (!$pass) {
            $this->error('Password wajib diisi.');
            return 1; // Gagal
        }

        /**
         * =========================================
         * 3) Membuat / mengambil user berdasarkan email
         * =========================================
         * firstOrNew():
         * - Jika user dengan email tersebut ada → ambil.
         * - Jika tidak ada → buat instance baru (belum disimpan).
         */
        $user = User::firstOrNew(['email' => $email]);

        /**
         * =========================================
         * 4) Set kolom secara manual
         * =========================================
         * Kenapa tidak boleh mass assignment?
         * - Karena mungkin ada kolom sensitif dan untuk lebih aman.
         */

        // Mengisi nama lengkap
        $user->name = $name;

        // Mengisi password yang telah di-hash (wajib pakai Hash::make)
        $user->password = Hash::make($pass);

        // Role ID wajib ada, tidak boleh null
        $user->role_id = $roleId;

        // Status aktif (1 = aktif)
        $user->status = 1;

        // Membuat remember_token acak sepanjang 60 karakter
        $user->remember_token = Str::random(60);

        /**
         * =========================================
         * 5) Menyimpan user
         * =========================================
         * Jika user baru → INSERT
         * Jika user sudah ada → UPDATE
         */
        $user->save();

        /**
         * =========================================
         * 6) Menampilkan output hasil pembuatan
         * =========================================
         * $this->info() → teks hijau.
         * $this->line() → teks normal.
         */
        $this->info("✅ Akun Dinkes berhasil dibuat/diupdate:");
        $this->line(" - Email : {$user->email}");
        $this->line(" - Nama  : {$user->name}");
        $this->line(" - Role  : {$roleId} (dinkes)");

        return 0; // sukses
    }
}
