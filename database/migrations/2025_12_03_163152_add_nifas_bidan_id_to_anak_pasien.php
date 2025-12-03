<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('anak_pasien', function (Blueprint $table) {
            if (!Schema::hasColumn('anak_pasien', 'nifas_bidan_id')) {
                $table->foreignId('nifas_bidan_id')
                      ->nullable()
                      ->constrained('pasien_nifas_bidan')
                      ->onUpdate('cascade')
                      ->onDelete('cascade');
            }
        });
        DB::statement('ALTER TABLE anak_pasien ALTER COLUMN nifas_id DROP NOT NULL');
    }

    public function down(): void
    {
        Schema::table('anak_pasien', function (Blueprint $table) {
            if (Schema::hasColumn('anak_pasien', 'nifas_bidan_id')) {
                $table->dropConstrainedForeignId('nifas_bidan_id');
            }
        });
        DB::statement('ALTER TABLE anak_pasien ALTER COLUMN nifas_id SET NOT NULL');
    }
};