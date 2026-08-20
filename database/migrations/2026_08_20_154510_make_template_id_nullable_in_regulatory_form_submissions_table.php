<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('regulatory_form_submissions', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->unsignedBigInteger('template_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('regulatory_form_submissions', function (Blueprint $table) {
            $table->unsignedBigInteger('template_id')->nullable(false)->change();
            $table->foreign('template_id')->references('id')->on('regulatory_form_templates')->onDelete('cascade');
        });
    }
};