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
        Schema::create('kuisioner_pasiens', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pertanyaan');
            $table->enum('status_soal', ['individu', 'keluarga', 'pre_eklampsia']);
            $table->enum('resiko', ['non-risk', 'sedang', 'tinggi']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuisioner_pasiens');
    }
};
