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
        Schema::create('pasien_nifas_bidan', function (Blueprint $table) {
            $table->id();
            // Catatan: Sesuai SQL, bidan_id merujuk ke puskesmas. Jika ini keliru, ubah 'puskesmas' menjadi 'bidans'.
            $table->foreignId('bidan_id')->constrained('puskesmas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('pasien_id')->constrained('pasiens')->onUpdate('cascade')->onDelete('cascade');
            $table->date('tanggal_mulai_nifas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasien_nifas_bidan');
    }
};
