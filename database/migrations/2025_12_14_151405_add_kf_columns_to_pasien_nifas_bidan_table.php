<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pasien_nifas_bidan', function (Blueprint $table) {
            // KF1
            $table->unsignedBigInteger('kf1_id')->nullable()->after('tanggal_mulai_nifas');
            $table->timestamp('kf1_tanggal')->nullable()->after('kf1_id');
            $table->text('kf1_catatan')->nullable()->after('kf1_tanggal');
            
            // KF2
            $table->unsignedBigInteger('kf2_id')->nullable()->after('kf1_catatan');
            $table->timestamp('kf2_tanggal')->nullable()->after('kf2_id');
            $table->text('kf2_catatan')->nullable()->after('kf2_tanggal');
            
            // KF3
            $table->unsignedBigInteger('kf3_id')->nullable()->after('kf2_catatan');
            $table->timestamp('kf3_tanggal')->nullable()->after('kf3_id');
            $table->text('kf3_catatan')->nullable()->after('kf3_tanggal');
            
            // KF4
            $table->unsignedBigInteger('kf4_id')->nullable()->after('kf3_catatan');
            $table->timestamp('kf4_tanggal')->nullable()->after('kf4_id');
            $table->text('kf4_catatan')->nullable()->after('kf4_tanggal');

            // Foreign keys ke tabel kf_kunjungans
            $table->foreign('kf1_id')->references('id')->on('kf_kunjungans')->onDelete('set null');
            $table->foreign('kf2_id')->references('id')->on('kf_kunjungans')->onDelete('set null');
            $table->foreign('kf3_id')->references('id')->on('kf_kunjungans')->onDelete('set null');
            $table->foreign('kf4_id')->references('id')->on('kf_kunjungans')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('pasien_nifas_bidan', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['kf1_id']);
            $table->dropForeign(['kf2_id']);
            $table->dropForeign(['kf3_id']);
            $table->dropForeign(['kf4_id']);
            
            // Drop columns
            $table->dropColumn([
                'kf1_id', 'kf1_tanggal', 'kf1_catatan',
                'kf2_id', 'kf2_tanggal', 'kf2_catatan',
                'kf3_id', 'kf3_tanggal', 'kf3_catatan',
                'kf4_id', 'kf4_tanggal', 'kf4_catatan',
            ]);
        });
    }
};