<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rujukan_rs', function (Blueprint $table) {
            // Field untuk form RS
            $table->boolean('pasien_datang')->nullable()->after('done_status');
            $table->text('riwayat_tekanan_darah')->nullable()->after('pasien_datang');
            $table->text('hasil_protein_urin')->nullable()->after('riwayat_tekanan_darah');
            $table->boolean('perlu_pemeriksaan_lanjut')->nullable()->after('hasil_protein_urin');
        });
    }

    public function down(): void
    {
        Schema::table('rujukan_rs', function (Blueprint $table) {
            $table->dropColumn([
                'pasien_datang',
                'riwayat_tekanan_darah',
                'hasil_protein_urin',
                'perlu_pemeriksaan_lanjut'
            ]);
        });
    }
};