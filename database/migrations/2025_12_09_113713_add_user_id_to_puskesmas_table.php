<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToPuskesmasTable extends Migration
{
    public function up()
    {
        Schema::table('puskesmas', function (Blueprint $table) {
            // Tambahkan kolom user_id
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            
            // Tambahkan foreign key constraint
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('puskesmas', function (Blueprint $table) {
            // Hapus foreign key dulu
            $table->dropForeign(['user_id']);
            
            // Hapus kolom
            $table->dropColumn('user_id');
        });
    }
}