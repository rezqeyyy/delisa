<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasien_nifas_rs', function (Blueprint $table) {
            // Tambah kolom KF4 setelah KF3
            $table->timestamp('kf4_tanggal')
                  ->nullable()
                  ->after('kf3_catatan');

            $table->text('kf4_catatan')
                  ->nullable()
                  ->after('kf4_tanggal');

            $table->unsignedBigInteger('kf4_id')
                  ->nullable()
                  ->after('kf3_id');

            // Opsional: kalau mau foreign key ke kf_kunjungans
            // $table->foreign('kf4_id')
            //       ->references('id')
            //       ->on('kf_kunjungans')
            //       ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pasien_nifas_rs', function (Blueprint $table) {
            // Kalau ada foreign key, drop dulu
            // $table->dropForeign(['kf4_id']);

            $table->dropColumn(['kf4_tanggal', 'kf4_catatan', 'kf4_id']);
        });
    }
};
