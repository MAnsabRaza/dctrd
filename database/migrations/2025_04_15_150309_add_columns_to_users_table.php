<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('frontend_link_gk')->nullable();
            $table->integer('front_iframe_height_gk')->nullable();
        });
    }


    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('frontend_link_gk');
            $table->dropColumn('front_iframe_height_gk');
        });
    }
};
