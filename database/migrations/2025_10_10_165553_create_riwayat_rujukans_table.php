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
        Schema::create('riwayat_rujukans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rujukan_id')->constrained('rujukan_rs')->onDelete('cascade');
            $table->foreignId('skrining_id')->constrained('skrinings')->onDelete('cascade');
            $table->date('tanggal_datang')->nullable();
            $table->string('tekanan_darah')->nullable();
            $table->enum('anjuran_kontrol', ['fktp', 'rs'])->nullable();
            $table->string('kunjungan_berikutnya')->nullable();
            $table->string('tindakan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_rujukans');
    }
};
