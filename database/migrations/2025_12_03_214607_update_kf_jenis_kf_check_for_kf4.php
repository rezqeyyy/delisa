<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus constraint lama yang hanya mengizinkan 1,2,3
        DB::statement('
            ALTER TABLE kf_kunjungans
            DROP CONSTRAINT IF EXISTS kf_kunjungans_jenis_kf_check
        ');

        // Tambahkan constraint baru yang mengizinkan 1–4
        DB::statement("
            ALTER TABLE kf_kunjungans
            ADD CONSTRAINT kf_kunjungans_jenis_kf_check
            CHECK (jenis_kf IN ('1', '2', '3', '4'))
        ");
    }

    public function down(): void
    {
        // Kembalikan ke constraint lama (hanya 1–3)
        DB::statement('
            ALTER TABLE kf_kunjungans
            DROP CONSTRAINT IF EXISTS kf_kunjungans_jenis_kf_check
        ');

        DB::statement("
            ALTER TABLE kf_kunjungans
            ADD CONSTRAINT kf_kunjungans_jenis_kf_check
            CHECK (jenis_kf IN ('1', '2', '3'))
        ");
    }
};
