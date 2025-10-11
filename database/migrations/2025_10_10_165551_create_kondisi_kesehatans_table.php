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
        Schema::create('kondisi_kesehatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skrining_id')->constrained('skrinings')->onDelete('cascade');
            $table->integer('tinggi_badan');
            $table->decimal('berat_badan_saat_hamil', 5, 2);
            $table->double('imt');
            $table->string('status_imt');
            $table->date('hpht')->nullable();
            $table->date('tanggal_skrining');
            $table->integer('usia_kehamilan');
            $table->date('tanggal_perkiraan_persalinan');
            $table->string('anjuran_kenaikan_bb');
            $table->integer('sdp')->default(0);
            $table->integer('dbp')->default(0);
            $table->decimal('map', 5, 2)->default(0);
            $table->enum('pemeriksaan_protein_urine', ['Negatif', 'Positif 1', 'Positif 2', 'Positif 3', 'Belum dilakukan Pemeriksaan'])->default('Belum dilakukan Pemeriksaan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kondisi_kesehatans');
    }
};
