<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CreateDinkesAdmin extends Command
{
    protected $signature = 'create:dinkes-admin';
    protected $description = 'Create or update the Dinkes master admin account securely';

    public function handle()
    {
        // Ambil ID role 'dinkes' langsung sebagai integer
        $roleId = DB::table('roles')->where('nama_role', 'dinkes')->value('id');

        if (!$roleId) {
            $this->error('Role "dinkes" belum ada di tabel roles. Tambahkan dulu.');
            return 1;
        }

        $email = $this->ask('Masukkan email admin Dinkes', 'admin@gmail.com');
        $name  = $this->ask('Masukkan nama lengkap admin', 'Admin Dinkes Kota Depok');
        $pass  = $this->secret('Masukkan password awal (tidak akan terlihat)');

        if (!$pass) {
            $this->error('Password wajib diisi.');
            return 1;
        }

        // Buat / ambil user berdasarkan email
        $user = User::firstOrNew(['email' => $email]);

        // Set kolom satu per satu (hindari mass assignment)
        $user->name           = $name;
        $user->password       = Hash::make($pass);
        $user->role_id        = $roleId;          // <— kunci: TIDAK boleh null
        $user->status         = 1;                // aktif
        $user->remember_token = Str::random(60);

        $user->save();

        $this->info("✅ Akun Dinkes berhasil dibuat/diupdate:");
        $this->line(" - Email : {$user->email}");
        $this->line(" - Nama  : {$user->name}");
        $this->line(" - Role  : {$roleId} (dinkes)");
        return 0;
    }
}
