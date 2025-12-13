<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToPuskesmasTable extends Migration
{
 public function up(): void
{
    Schema::table('puskesmas', function (Blueprint $table) {
        if (!Schema::hasColumn('puskesmas', 'user_id')) {
            $table->unsignedBigInteger('user_id')->nullable();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        }
    });
}

}