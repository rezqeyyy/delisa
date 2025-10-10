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
        Schema::create('rujukan_rs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pasien_id')->constrained('pasiens')->onDelete('cascade');
        $table->foreignId('rs_id')->constrained('rumah_sakits')->onDelete('cascade');
        $table->foreignId('skrining_id')->constrained('skrinings')->onDelete('cascade');
        $table->boolean('done_status')->default(false);
        $table->text('catatan_rujukan')->nullable();
        $table->boolean('is_rujuk')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rujukan_rs');
    }
};
