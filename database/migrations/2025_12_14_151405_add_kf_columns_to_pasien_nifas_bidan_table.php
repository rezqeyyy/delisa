<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pasien_nifas_bidan', function (Blueprint $table) {
            // Tambah kolom KF1 s/d KF4 hanya jika belum ada
            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf1_id')) {
                $table->unsignedBigInteger('kf1_id')->nullable()->after('tanggal_mulai_nifas');
            }
            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf1_tanggal')) {
                $table->timestamp('kf1_tanggal')->nullable()->after('kf1_id');
            }
            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf1_catatan')) {
                $table->text('kf1_catatan')->nullable()->after('kf1_tanggal');
            }

            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf2_id')) {
                $table->unsignedBigInteger('kf2_id')->nullable()->after('kf1_catatan');
            }
            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf2_tanggal')) {
                $table->timestamp('kf2_tanggal')->nullable()->after('kf2_id');
            }
            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf2_catatan')) {
                $table->text('kf2_catatan')->nullable()->after('kf2_tanggal');
            }

            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf3_id')) {
                $table->unsignedBigInteger('kf3_id')->nullable()->after('kf2_catatan');
            }
            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf3_tanggal')) {
                $table->timestamp('kf3_tanggal')->nullable()->after('kf3_id');
            }
            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf3_catatan')) {
                $table->text('kf3_catatan')->nullable()->after('kf3_tanggal');
            }

            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf4_id')) {
                $table->unsignedBigInteger('kf4_id')->nullable()->after('kf3_catatan');
            }
            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf4_tanggal')) {
                $table->timestamp('kf4_tanggal')->nullable()->after('kf4_id');
            }
            if (!Schema::hasColumn('pasien_nifas_bidan', 'kf4_catatan')) {
                $table->text('kf4_catatan')->nullable()->after('kf4_tanggal');
            }

            // Tambah foreign key dengan proteksi (abaikan jika sudah ada)
            try { $table->foreign('kf1_id')->references('id')->on('kf_kunjungans')->onDelete('set null'); } catch (\Throwable $e) {}
            try { $table->foreign('kf2_id')->references('id')->on('kf_kunjungans')->onDelete('set null'); } catch (\Throwable $e) {}
            try { $table->foreign('kf3_id')->references('id')->on('kf_kunjungans')->onDelete('set null'); } catch (\Throwable $e) {}
            try { $table->foreign('kf4_id')->references('id')->on('kf_kunjungans')->onDelete('set null'); } catch (\Throwable $e) {}
        });
    }

    public function down()
    {
        Schema::table('pasien_nifas_bidan', function (Blueprint $table) {
            // Drop foreign keys jika ada
            try { $table->dropForeign(['kf1_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['kf2_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['kf3_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['kf4_id']); } catch (\Throwable $e) {}

            // Drop kolom jika ada
            foreach ([
                'kf1_tanggal','kf1_catatan','kf1_id',
                'kf2_tanggal','kf2_catatan','kf2_id',
                'kf3_tanggal','kf3_catatan','kf3_id',
                'kf4_tanggal','kf4_catatan','kf4_id',
            ] as $col) {
                if (Schema::hasColumn('pasien_nifas_bidan', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};