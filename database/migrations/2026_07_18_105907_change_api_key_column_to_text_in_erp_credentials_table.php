<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_credentials', function (Blueprint $table) {
            $table->text('api_key')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('erp_credentials', function (Blueprint $table) {
            $table->string('api_key')->nullable()->change();
        });
    }
};