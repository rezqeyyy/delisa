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
        Schema::create('riwayat_kehamilans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skrining_id')->constrained('skrinings')->onDelete('cascade');
            $table->foreignId('pasien_id')->constrained('pasiens')->onDelete('cascade');
            $table->integer('kehamilan');
            $table->integer('tahun_kehamilan');
            $table->string('pengalaman_kehamilan');
            $table->decimal('berat_lahir', 5, 2)->nullable();
            $table->string('kondisi_bayi')->nullable();
            $table->string('jenis_persalinan')->nullable();
            $table->string('penolong_persalinan')->nullable();
            $table->string('komplikasi')->default('Tidak Ada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_kehamilans');
    }
};
