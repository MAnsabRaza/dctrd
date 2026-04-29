<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('backend_link')->nullable()->after('email');
            $table->string('frontend_link')->nullable()->after('backend_link');
        });
    }


    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['backend_link', 'frontend_link']);
        });
    }
};
