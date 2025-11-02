<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Puskesmas;

class PuskesmasTempSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan role 'puskesmas' tersedia (ID = 4)
        $this->call(RoleSeederSelainDinkes::class);

        // Buat Puskesmas sementara untuk bisa dipilih di modal skrining
        $pusk = Puskesmas::firstOrCreate(
            ['nama_puskesmas' => 'Puskesmas Sementara'],
            [
                'lokasi'     => 'Jl. Contoh No. 1',
                'kecamatan'  => 'Contoh',
                'is_mandiri' => false,
            ]
        );

        // Buat akun user role puskesmas (tidak ada FK ke tabel puskesmas saat ini)
        User::updateOrCreate(
            ['email' => 'puskesmas.temp@example.com'],
            [
                'name'     => 'Puskesmas Sementara',
                'password' => Hash::make('delisa123'),
                'phone'    => '081234567890',
                'address'  => 'Jl. Contoh No. 1',
                'status'   => true,
                'role_id'  => 4, // puskesmas
            ]
        );
    }
}