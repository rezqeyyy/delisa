<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop foreign key lama
        Schema::table('anak_pasien', function (Blueprint $table) {
            $table->dropForeign(['nifas_id']);
        });

        // Tambah foreign key baru yang benar
        Schema::table('anak_pasien', function (Blueprint $table) {
            $table->foreign('nifas_id')
                  ->references('id')
                  ->on('pasien_nifas_rs')
                  ->onDelete('cascade');
        });
        
        // Tambah kolom yang kurang
        Schema::table('anak_pasien', function (Blueprint $table) {
            $table->json('riwayat_penyakit')->nullable()->after('imd');
            $table->text('keterangan_masalah_lain')->nullable()->after('riwayat_penyakit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
    }
};