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
        Schema::create('riwayat_kehamilan_gpas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skrining_id')->constrained('skrinings')->onDelete('cascade');
            $table->foreignId('pasien_id')->constrained('pasiens')->onDelete('cascade');
            $table->string('total_kehamilan')->nullable();
            $table->string('total_persalinan')->nullable();
            $table->string('total_abortus')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_kehamilan_gpas');
    }
};
