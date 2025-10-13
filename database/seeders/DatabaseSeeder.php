<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 1) Seed roles
        $this->call([
            RoleSeederDinkes::class,
            RoleSeederSelainDinkes::class,
        ]);

        // 2) Buat admin Dinkes (updateOrInsert untuk idempotent)
        $roleId = DB::table('roles')->where('nama_role', 'dinkes')->value('id');

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@gmail.com'],
            [
                'name'       => 'Test Dinkes',
                'password'   => Hash::make('admin123'),
                'role_id'    => $roleId,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
