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
        Schema::create('rujukan_nifas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kf')->constrained('kf')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('rs_id')->constrained('rumah_sakits')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('status_rujukan');
            $table->date('tanggal_rujukan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rujukan_nifas');
    }
};
