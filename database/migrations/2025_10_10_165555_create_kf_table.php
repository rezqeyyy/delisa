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
        Schema::create('kf', function (Blueprint $table) {
            $table->id();
            // Catatan: Sesuai SQL, id_nifas merujuk ke pasiens.
            $table->foreignId('id_nifas')->constrained('pasiens')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('id_anak')->constrained('anak_pasien')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('kunjungan_nifas_ke');
            $table->date('tanggal_kunjungan')->default(now());
            $table->bigInteger('sbp');
            $table->bigInteger('dbp');
            $table->bigInteger('map');
            $table->string('keadaan_umum')->nullable();
            $table->string('tanda_bahaya')->nullable();
            $table->enum('kesimpulan_pantauan', ['Sehat', 'Dirujuk', 'Meninggal']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kf');
    }
};
