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
        Schema::table('pasien_nifas_rs', function (Blueprint $table) {
            // Tambah kolom tanggal melahirkan jika belum ada
            $table->date('tanggal_melahirkan')->nullable()->after('tanggal_mulai_nifas');
            
            // Kolom KF1
            $table->timestamp('kf1_tanggal')->nullable();
            $table->text('kf1_catatan')->nullable();
            
            // Kolom KF2
            $table->timestamp('kf2_tanggal')->nullable();
            $table->text('kf2_catatan')->nullable();
            
            // Kolom KF3
            $table->timestamp('kf3_tanggal')->nullable();
            $table->text('kf3_catatan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasien_nifas_rs', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_melahirkan',
                'kf1_tanggal', 'kf1_catatan',
                'kf2_tanggal', 'kf2_catatan',
                'kf3_tanggal', 'kf3_catatan',
            ]);
        });
    }
};