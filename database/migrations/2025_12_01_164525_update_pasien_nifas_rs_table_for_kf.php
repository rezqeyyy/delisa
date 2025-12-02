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
            // ========== UBAH KOLOM LAMA (BACKWARD COMPATIBILITY) ==========
            // Ubah kolom KF lama menjadi nullable
            // Note: Cek dulu apakah kolom ini ada di tabel Anda
            if (Schema::hasColumn('pasien_nifas_rs', 'kf1_tanggal')) {
                $table->timestamp('kf1_tanggal')->nullable()->change();
            }
            if (Schema::hasColumn('pasien_nifas_rs', 'kf1_catatan')) {
                $table->text('kf1_catatan')->nullable()->change();
            }
            if (Schema::hasColumn('pasien_nifas_rs', 'kf2_tanggal')) {
                $table->timestamp('kf2_tanggal')->nullable()->change();
            }
            if (Schema::hasColumn('pasien_nifas_rs', 'kf2_catatan')) {
                $table->text('kf2_catatan')->nullable()->change();
            }
            if (Schema::hasColumn('pasien_nifas_rs', 'kf3_tanggal')) {
                $table->timestamp('kf3_tanggal')->nullable()->change();
            }
            if (Schema::hasColumn('pasien_nifas_rs', 'kf3_catatan')) {
                $table->text('kf3_catatan')->nullable()->change();
            }
            
            // ========== TAMBAH FOREIGN KEYS KE TABEL BARU ==========
            // Tambah kolom untuk relasi ke kf_kunjungans
            $table->foreignId('kf1_id')
                  ->nullable()
                  ->constrained('kf_kunjungans')
                  ->nullOnDelete();
            
            $table->foreignId('kf2_id')
                  ->nullable()
                  ->constrained('kf_kunjungans')
                  ->nullOnDelete();
            
            $table->foreignId('kf3_id')
                  ->nullable()
                  ->constrained('kf_kunjungans')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasien_nifas_rs', function (Blueprint $table) {
            // ========== HAPUS FOREIGN KEYS ==========
            $table->dropForeign(['kf1_id']);
            $table->dropForeign(['kf2_id']);
            $table->dropForeign(['kf3_id']);
            
            // ========== HAPUS KOLOM BARU ==========
            $table->dropColumn(['kf1_id', 'kf2_id', 'kf3_id']);
            
            // ========== KEMBALIKAN KOLOM LAMA (OPSIONAL) ==========
            // Jika ingin kembalikan ke not nullable
            // $table->timestamp('kf1_tanggal')->nullable(false)->change();
            // $table->text('kf1_catatan')->nullable(false)->change();
            // ... dst
        });
    }
};