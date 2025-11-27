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
        Schema::table('anak_pasien', function (Blueprint $table) {
            $table->enum('kondisi_ibu', ['aman', 'perlu_tindak_lanjut'])->nullable()->after('keterangan_masalah_lain');
            $table->text('catatan_kondisi_ibu')->nullable()->after('kondisi_ibu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anak_pasien', function (Blueprint $table) {
            $table->dropColumn(['kondisi_ibu', 'catatan_kondisi_ibu']);
        });
    }
};