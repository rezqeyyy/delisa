<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * ==========================================================
         * 1) MIGRASI PUSKESMAS  (safe block)
         * ==========================================================
         */
        try {
            Schema::table('puskesmas', function (Blueprint $table) {
                // tidak ada perubahan
            });
        } catch (\Throwable $e) {
            Log::error("Skip MIGRASI PUSKESMAS: " . $e->getMessage());
        }


        /**
         * ==========================================================
         * 2) MIGRASI RUJUKAN_RS
         * ==========================================================
         */
        try {
            Schema::table('rujukan_rs', function (Blueprint $table) {
                $table->boolean('pasien_datang')->nullable()->after('done_status');
                $table->text('riwayat_tekanan_darah')->nullable()->after('pasien_datang');
                $table->text('hasil_protein_urin')->nullable()->after('riwayat_tekanan_darah');
                $table->boolean('perlu_pemeriksaan_lanjut')->nullable()->after('hasil_protein_urin');
            });
        } catch (\Throwable $e) {
            Log::error("Skip RUJUKAN_RS: " . $e->getMessage());
        }


        /**
         * ==========================================================
         * 3) MIGRASI RESEP_OBATS
         * ==========================================================
         */
        try {

            DB::statement('ALTER TABLE "resep_obats" DROP CONSTRAINT IF EXISTS "resep_obats_riwayat_rujukan_id_foreign";');

            if (Schema::hasColumn('resep_obats', 'riwayat_rujukan_id') &&
                !Schema::hasColumn('resep_obats', 'rujukan_rs_id')) {

                Schema::table('resep_obats', function (Blueprint $table) {
                    $table->renameColumn('riwayat_rujukan_id', 'rujukan_rs_id');
                });
            }

            if (!Schema::hasColumn('resep_obats', 'rujukan_rs_id')) {
                Schema::table('resep_obats', function (Blueprint $table) {
                    $table->unsignedBigInteger('rujukan_rs_id')->nullable()->after('id');
                });
            }

            DB::statement('ALTER TABLE "resep_obats" DROP CONSTRAINT IF EXISTS "resep_obats_rujukan_rs_id_foreign";');

            DB::statement('
                ALTER TABLE "resep_obats"
                ADD CONSTRAINT "resep_obats_rujukan_rs_id_foreign"
                FOREIGN KEY ("rujukan_rs_id")
                REFERENCES "rujukan_rs" ("id")
                ON DELETE CASCADE;
            ');

        } catch (\Throwable $e) {
            Log::error("Skip RESEP_OBATS: " . $e->getMessage());
        }


        /**
         * ==========================================================
         * 4) MIGRASI ANAK_PASIEN
         * ==========================================================
         */
        try {
            // Drop FK lama
            Schema::table('anak_pasien', function (Blueprint $table) {
                $table->dropForeign(['nifas_id']);
            });

            // FK baru
            Schema::table('anak_pasien', function (Blueprint $table) {
                $table->foreign('nifas_id')
                      ->references('id')
                      ->on('pasien_nifas_rs')
                      ->onDelete('cascade');
            });

            // Tambah kolom baru
            Schema::table('anak_pasien', function (Blueprint $table) {
                $table->json('riwayat_penyakit')->nullable()->after('imd');
                $table->text('keterangan_masalah_lain')->nullable()->after('riwayat_penyakit');
            });

        } catch (\Throwable $e) {
            Log::error("Skip ANAK_PASIEN: " . $e->getMessage());
        }
    }


    /**
     * Rollback juga dibuat aman agar tidak menghentikan proses.
     */
    public function down(): void
    {
        // Semuanya di‐wrap per blok TRY–CATCH
        try {
            Schema::table('rujukan_rs', function (Blueprint $table) {
                $table->dropColumn([
                    'pasien_datang',
                    'riwayat_tekanan_darah',
                    'hasil_protein_urin',
                    'perlu_pemeriksaan_lanjut'
                ]);
            });
        } catch (\Throwable $e) {
            Log::error("Skip rollback RUJUKAN_RS: " . $e->getMessage());
        }

        try {
            DB::statement('ALTER TABLE "resep_obats" DROP CONSTRAINT IF EXISTS "resep_obats_rujukan_rs_id_foreign";');

            if (Schema::hasColumn('resep_obats', 'rujukan_rs_id') &&
                !Schema::hasColumn('resep_obats', 'riwayat_rujukan_id')) {

                Schema::table('resep_obats', function (Blueprint $table) {
                    $table->renameColumn('rujukan_rs_id', 'riwayat_rujukan_id');
                });
            }

            if (Schema::hasColumn('resep_obats', 'riwayat_rujukan_id')) {
                DB::statement('
                    ALTER TABLE "resep_obats"
                    ADD CONSTRAINT "resep_obats_riwayat_rujukan_id_foreign"
                    FOREIGN KEY ("riwayat_rujukan_id")
                    REFERENCES "riwayat_rujukans" ("id")
                    ON DELETE CASCADE;
                ');
            }

        } catch (\Throwable $e) {
            Log::error("Skip rollback RESEP_OBATS: " . $e->getMessage());
        }

        try {
            Schema::table('anak_pasien', function (Blueprint $table) {
                $table->dropForeign(['nifas_id']);
            });

            Schema::table('anak_pasien', function (Blueprint $table) {
                $table->foreign('nifas_id')
                      ->references('id')
                      ->on('pasiens')
                      ->onDelete('cascade');
            });

            Schema::table('anak_pasien', function (Blueprint $table) {
                $table->dropColumn(['riwayat_penyakit', 'keterangan_masalah_lain']);
            });

        } catch (\Throwable $e) {
            Log::error("Skip rollback ANAK_PASIEN: " . $e->getMessage());
        }
    }
};

