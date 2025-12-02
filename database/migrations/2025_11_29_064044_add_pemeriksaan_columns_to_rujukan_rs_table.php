<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rujukan_rs', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada atau belum
            if (!Schema::hasColumn('rujukan_rs', 'pasien_datang')) {
                $table->boolean('pasien_datang')->nullable();
            }
            
            if (!Schema::hasColumn('rujukan_rs', 'perlu_pemeriksaan_lanjut')) {
                $table->boolean('perlu_pemeriksaan_lanjut')->nullable();
            }
            
            if (!Schema::hasColumn('rujukan_rs', 'riwayat_tekanan_darah')) {
                $table->string('riwayat_tekanan_darah')->nullable();
            }
            
            if (!Schema::hasColumn('rujukan_rs', 'hasil_protein_urin')) {
                $table->string('hasil_protein_urin')->nullable();
            }
            
            if (!Schema::hasColumn('rujukan_rs', 'catatan_rujukan')) {
                $table->text('catatan_rujukan')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rujukan_rs', function (Blueprint $table) {
            $columns = [
                'pasien_datang',
                'perlu_pemeriksaan_lanjut',
                'riwayat_tekanan_darah',
                'hasil_protein_urin',
                'catatan_rujukan'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('rujukan_rs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};