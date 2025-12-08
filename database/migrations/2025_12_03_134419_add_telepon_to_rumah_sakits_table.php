<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('rumah_sakits', function (Blueprint $table) {
            $table->string('telepon')->nullable();
        });
    }

    public function down()
    {
        Schema::table('rumah_sakits', function (Blueprint $table) {
            $table->dropColumn('telepon');
        });
    }
};
