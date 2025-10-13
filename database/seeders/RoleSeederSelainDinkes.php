<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeederSelainDinkes extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['id' => 2, 'nama_role' => 'bidan'],
            ['id' => 3, 'nama_role' => 'rs'],
            ['id' => 4, 'nama_role' => 'puskesmas'],
        ]);
    }
}
