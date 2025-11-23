<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Drop foreign key lama (kalau ada) dengan IF EXISTS biar nggak error
        DB::statement('ALTER TABLE "resep_obats" DROP CONSTRAINT IF EXISTS "resep_obats_riwayat_rujukan_id_foreign";');

        // 2) Rename column kalau masih pakai nama lama
        if (Schema::hasColumn('resep_obats', 'riwayat_rujukan_id') &&
            !Schema::hasColumn('resep_obats', 'rujukan_rs_id')) {

            Schema::table('resep_obats', function (Blueprint $table) {
                $table->renameColumn('riwayat_rujukan_id', 'rujukan_rs_id');
            });
        }

        // 3) Kalau ternyata kolom baru belum ada sama sekali (misalnya di DB kosong),
        //    kita pastikan ada kolom rujuan_rs_id
        if (!Schema::hasColumn('resep_obats', 'rujukan_rs_id')) {
            Schema::table('resep_obats', function (Blueprint $table) {
                $table->unsignedBigInteger('rujukan_rs_id')->nullable()->after('id');
            });
        }

        // 4) Tambahkan foreign key baru ke rujukan_rs (kalau belum ada)
        DB::statement('
            ALTER TABLE "resep_obats"
            DROP CONSTRAINT IF EXISTS "resep_obats_rujukan_rs_id_foreign";
        ');

        DB::statement('
            ALTER TABLE "resep_obats"
            ADD CONSTRAINT "resep_obats_rujukan_rs_id_foreign"
            FOREIGN KEY ("rujukan_rs_id")
            REFERENCES "rujukan_rs" ("id")
            ON DELETE CASCADE;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1) Drop FK ke rujukan_rs kalau ada
        DB::statement('
            ALTER TABLE "resep_obats"
            DROP CONSTRAINT IF EXISTS "resep_obats_rujukan_rs_id_foreign";
        ');

        // 2) Rename balik ke nama lama kalau kolomnya ada
        if (Schema::hasColumn('resep_obats', 'rujukan_rs_id') &&
            !Schema::hasColumn('resep_obats', 'riwayat_rujukan_id')) {

            Schema::table('resep_obats', function (Blueprint $table) {
                $table->renameColumn('rujukan_rs_id', 'riwayat_rujukan_id');
            });
        }

        // 3) Tambahkan lagi FK lama ke riwayat_rujukans (kalau kolomnya ada)
        if (Schema::hasColumn('resep_obats', 'riwayat_rujukan_id')) {
            DB::statement('
                ALTER TABLE "resep_obats"
                ADD CONSTRAINT "resep_obats_riwayat_rujukan_id_foreign"
                FOREIGN KEY ("riwayat_rujukan_id")
                REFERENCES "riwayat_rujukans" ("id")
                ON DELETE CASCADE;
            ');
        }
    }
};
