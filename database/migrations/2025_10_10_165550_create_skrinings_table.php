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
        Schema::create('skrinings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasiens')->onDelete('cascade');
            $table->foreignId('puskesmas_id')->constrained('puskesmas')->onDelete('cascade');
            $table->string('status_pre_eklampsia')->nullable();
            $table->integer('jumlah_resiko_sedang')->nullable();
            $table->integer('jumlah_resiko_tinggi')->nullable();
            $table->string('kesimpulan')->nullable();
            $table->integer('step_form')->nullable();
            $table->boolean('tindak_lanjut')->default(false);
            $table->boolean('checked_status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skrinings');
    }
};
