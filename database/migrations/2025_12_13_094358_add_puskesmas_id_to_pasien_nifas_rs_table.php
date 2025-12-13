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
    Schema::table('pasien_nifas_rs', function (Blueprint $table) {
        $table->unsignedBigInteger('puskesmas_id')
              ->nullable()
              ->after('rs_id');

        $table->foreign('puskesmas_id')
              ->references('id')
              ->on('puskesmas')
              ->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('pasien_nifas_rs', function (Blueprint $table) {
        $table->dropForeign(['puskesmas_id']);
        $table->dropColumn('puskesmas_id');
    });
}

};
