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
        Schema::table('resep_obats', function (Blueprint $table) {
            // Tambah kolom kalau belum ada
            if (!Schema::hasColumn('resep_obats', 'riwayat_rujukan_id')) {
                $table->unsignedBigInteger('riwayat_rujukan_id')
                    ->nullable() // dibuat nullable supaya aman kalau sudah ada data lama
                    ->after('id');

                // Foreign key ke riwayat_rujukans (kalau tabelnya ada)
                $table->foreign('riwayat_rujukan_id', 'resep_obats_riwayat_rujukan_id_foreign')
                    ->references('id')
                    ->on('riwayat_rujukans')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resep_obats', function (Blueprint $table) {
            if (Schema::hasColumn('resep_obats', 'riwayat_rujukan_id')) {
                $table->dropForeign('resep_obats_riwayat_rujukan_id_foreign');
                $table->dropColumn('riwayat_rujukan_id');
            }
        });
    }
};
