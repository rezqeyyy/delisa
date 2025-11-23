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
         * 1) MIGRASI PUSKESMAS  (tambah user_id + FK, dibuat aman)
         * ==========================================================
         */
        try {
            // Pastikan tabel puskesmas ada dulu
            if (Schema::hasTable('puskesmas')) {

                // 1a. Tambah kolom user_id jika belum ada
                if (!Schema::hasColumn('puskesmas', 'user_id')) {
                    Schema::table('puskesmas', function (Blueprint $table) {
                        $table->unsignedBigInteger('user_id')->after('id');
                    });
                }

                // 1b. Drop FK lama kalau ada, lalu buat FK baru yang benar
                DB::statement('
                    ALTER TABLE "puskesmas"
                    DROP CONSTRAINT IF EXISTS "puskesmas_user_id_foreign";
                ');

                DB::statement('
                    ALTER TABLE "puskesmas"
                    ADD CONSTRAINT "puskesmas_user_id_foreign"
                    FOREIGN KEY ("user_id") REFERENCES "users" ("id")
                    ON DELETE CASCADE;
                ');
            }
        } catch (\Throwable $e) {
            Log::error('Skip MIGRASI PUSKESMAS (user_id): ' . $e->getMessage());
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
            // Drop constraint lama kalau ada
            DB::statement('
                ALTER TABLE "resep_obats"
                DROP CONSTRAINT IF EXISTS "resep_obats_riwayat_rujukan_id_foreign";
            ');

            // Rename kolom kalau masih pakai nama lama
            if (Schema::hasColumn('resep_obats', 'riwayat_rujukan_id') &&
                !Schema::hasColumn('resep_obats', 'rujukan_rs_id')) {

                Schema::table('resep_obats', function (Blueprint $table) {
                    $table->renameColumn('riwayat_rujukan_id', 'rujukan_rs_id');
                });
            }

            // Kalau ternyata belum ada kolom rujukan_rs_id sama sekali → buat baru
            if (!Schema::hasColumn('resep_obats', 'rujukan_rs_id')) {
                Schema::table('resep_obats', function (Blueprint $table) {
                    $table->unsignedBigInteger('rujukan_rs_id')->nullable()->after('id');
                });
            }

            // Pastikan constraint FK baru yang dipakai
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
        } catch (\Throwable $e) {
            Log::error("Skip RESEP_OBATS: " . $e->getMessage());
        }

        /**
         * ==========================================================
         * 4) MIGRASI ANAK_PASIEN
         * ==========================================================
         */
        try {
            // Drop FK lama (ke pasiens)
            Schema::table('anak_pasien', function (Blueprint $table) {
                $table->dropForeign(['nifas_id']);
            });

            // FK baru ke pasien_nifas_rs
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

    public function down(): void
    {
        /**
         * Rollback PUSKESMAS.user_id (aman)
         */
        try {
            if (Schema::hasTable('puskesmas')) {
                // Drop FK dulu
                DB::statement('
                    ALTER TABLE "puskesmas"
                    DROP CONSTRAINT IF EXISTS "puskesmas_user_id_foreign";
                ');

                // Baru buang kolom kalau memang ada
                if (Schema::hasColumn('puskesmas', 'user_id')) {
                    Schema::table('puskesmas', function (Blueprint $table) {
                        $table->dropColumn('user_id');
                    });
                }
            }
        } catch (\Throwable $e) {
            Log::error('Skip rollback PUSKESMAS (user_id): ' . $e->getMessage());
        }

        /**
         * Rollback RUJUKAN_RS
         */
        try {
            Schema::table('rujukan_rs', function (Blueprint $table) {
                $table->dropColumn([
                    'pasien_datang',
                    'riwayat_tekanan_darah',
                    'hasil_protein_urin',
                    'perlu_pemeriksaan_lanjut',
                ]);
            });
        } catch (\Throwable $e) {
            Log::error("Skip rollback RUJUKAN_RS: " . $e->getMessage());
        }

        /**
         * Rollback RESEP_OBATS
         */
        try {
            DB::statement('
                ALTER TABLE "resep_obats"
                DROP CONSTRAINT IF EXISTS "resep_obats_rujukan_rs_id_foreign";
            ');

            // Balik nama kolom kalau masih pakai rujukan_rs_id
            if (Schema::hasColumn('resep_obats', 'rujukan_rs_id') &&
                !Schema::hasColumn('resep_obats', 'riwayat_rujukan_id')) {

                Schema::table('resep_obats', function (Blueprint $table) {
                    $table->renameColumn('rujukan_rs_id', 'riwayat_rujukan_id');
                });
            }

            // Kalau sudah balik ke riwayat_rujukan_id, pasang lagi FK lama
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

        /**
         * Rollback ANAK_PASIEN
         */
        try {
            // Balik FK ke pasiens
            Schema::table('anak_pasien', function (Blueprint $table) {
                $table->dropForeign(['nifas_id']);
            });

            Schema::table('anak_pasien', function (Blueprint $table) {
                $table->foreign('nifas_id')
                      ->references('id')
                      ->on('pasiens')
                      ->onDelete('cascade');
            });

            // Hapus kolom tambahan
            Schema::table('anak_pasien', function (Blueprint $table) {
                $table->dropColumn(['riwayat_penyakit', 'keterangan_masalah_lain']);
            });
        } catch (\Throwable $e) {
            Log::error("Skip rollback ANAK_PASIEN: " . $e->getMessage());
        }
    }
};
