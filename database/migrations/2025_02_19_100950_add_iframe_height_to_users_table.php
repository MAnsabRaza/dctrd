<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('front_iframe_height')->nullable()->after('frontend_link');
            $table->integer('back_iframe_height')->nullable()->after('backend_link');
        });
    }


    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['front_iframe_height', 'back_iframe_height']);
        });
    }
};
