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
        Schema::create('anak_pasien', function (Blueprint $table) {
            $table->id();
            // Catatan: Sesuai SQL, nifas_id merujuk ke pasiens.
            $table->foreignId('nifas_id')->constrained('pasiens')->onDelete('cascade');
            $table->integer('anak_ke');
            $table->date('tanggal_lahir');
            $table->string('jenis_kelamin');
            $table->string('nama_anak');
            $table->string('usia_kehamilan_saat_lahir');
            $table->decimal('berat_lahir_anak', 8, 2);
            $table->decimal('panjang_lahir_anak', 8, 2);
            $table->decimal('lingkar_kepala_anak', 8, 2);
            $table->boolean('memiliki_buku_kia')->default(false);
            $table->boolean('buku_kia_bayi_kecil')->default(false);
            $table->boolean('imd')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anak_pasien');
    }
};
