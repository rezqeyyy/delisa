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
        Schema::create('kf_kunjungans', function (Blueprint $table) {
            // ========== PRIMARY KEY ==========
            $table->id(); // ← INI WAJIB! Tanpa ini foreign key error
            
            // ========== REFERENSI ==========
            $table->foreignId('pasien_nifas_id')
                  ->constrained('pasien_nifas_rs')
                  ->onDelete('cascade');
            
            $table->enum('jenis_kf', [1, 2, 3]); // KF1, KF2, KF3
            
            // ========== FIELD DARI DOKUMEN PKM ==========
            // 1. Tanggal Kunjungan
            $table->date('tanggal_kunjungan');
            
            // 2. Tekanan Darah (Tensi)
            $table->integer('sbp')->nullable()->comment('Systolic Blood Pressure (mmHg)');
            $table->integer('dbp')->nullable()->comment('Diastolic Blood Pressure (mmHg)');
            $table->integer('map')->nullable()->comment('Mean Arterial Pressure (mmHg)');
            
            // 3. Keadaan Umum ibu
            $table->text('keadaan_umum')->nullable();
            
            // 4. Tanda Bahaya
            $table->text('tanda_bahaya')->nullable();
            
            // 5. Kesimpulan Pantauan
            $table->enum('kesimpulan_pantauan', ['Sehat', 'Dirujuk', 'Meninggal']);
            
            // 6. Catatan tambahan
            $table->text('catatan')->nullable();
            
            // ========== METADATA ==========
            $table->timestamps();
            
            // ========== CONSTRAINTS ==========
            // Satu pasien hanya boleh punya satu KF per jenis
            $table->unique(['pasien_nifas_id', 'jenis_kf']);
            
            // Index untuk performa query
            $table->index('jenis_kf');
            $table->index('tanggal_kunjungan');
            $table->index('kesimpulan_pantauan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kf_kunjungans');
    }
};