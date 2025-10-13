<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeederDinkes extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['id' => 2, 'nama_role' => 'bidan']
        ]);
    }
}
