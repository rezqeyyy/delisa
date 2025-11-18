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
        // Drop foreign key constraint lama
        Schema::table('resep_obats', function (Blueprint $table) {
            $table->dropForeign(['riwayat_rujukan_id']);
        });

        // Rename column untuk lebih jelas
        Schema::table('resep_obats', function (Blueprint $table) {
            $table->renameColumn('riwayat_rujukan_id', 'rujukan_rs_id');
        });

        // Tambah foreign key baru yang benar
        Schema::table('resep_obats', function (Blueprint $table) {
            $table->foreign('rujukan_rs_id')
                  ->references('id')
                  ->on('rujukan_rs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resep_obats', function (Blueprint $table) {
            $table->dropForeign(['rujukan_rs_id']);
        });

        Schema::table('resep_obats', function (Blueprint $table) {
            $table->renameColumn('rujukan_rs_id', 'riwayat_rujukan_id');
        });

        Schema::table('resep_obats', function (Blueprint $table) {
            $table->foreign('riwayat_rujukan_id')
                  ->references('id')
                  ->on('riwayat_rujukans')
                  ->onDelete('cascade');
        });
    }
};