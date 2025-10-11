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
        Schema::create('riwayat_penyakit_nifas', function (Blueprint $table) {
            $table->id();
            // Catatan: Sesuai SQL, nifas_id merujuk ke pasiens.
            $table->foreignId('nifas_id')->constrained('pasiens')->onDelete('cascade');
            $table->foreignId('anak_pasien_id')->constrained('anak_pasien')->onDelete('cascade');
            $table->string('nama_penyakit')->nullable();
            $table->text('keterangan_penyakit_lain')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_penyakit_nifas');
    }
};
