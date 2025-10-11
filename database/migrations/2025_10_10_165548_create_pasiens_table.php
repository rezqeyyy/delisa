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
        Schema::create('pasiens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nik', 16);
            $table->string('tempat_lahir', 50)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->boolean('status_perkawinan')->nullable();
            $table->string('PKecamatan')->nullable();
            $table->string('PKabupaten')->nullable();
            $table->string('PProvinsi')->nullable();
            $table->string('PPelayanan')->nullable();
            $table->string('PKarakteristik')->nullable();
            $table->string('PWilayah')->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('pekerjaan', 50)->nullable();
            $table->string('pendidikan', 20)->nullable();
            $table->string('pembiayaan_kesehatan')->nullable();
            $table->string('golongan_darah')->nullable();
            $table->string('no_jkn')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasiens');
    }
};
