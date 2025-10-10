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
        Schema::create('jawaban_kuisioners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuisioner_id')->constrained('kuisioner_pasiens')->onDelete('cascade');
            $table->boolean('jawaban')->default(false);
            $table->string('jawaban_lainnya')->nullable();
            $table->foreignId('skrining_id')->constrained('skrinings')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_kuisioners');
    }
};
