<?php
// database/migrations/2026_08_19_000000_add_form_id_to_regulatory_form_submissions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('regulatory_form_submissions', function (Blueprint $table) {
            $table->unsignedBigInteger('form_id')->nullable()->after('template_id');
            $table->index('form_id');
        });
    }

    public function down()
    {
        Schema::table('regulatory_form_submissions', function (Blueprint $table) {
            $table->dropIndex(['form_id']);
            $table->dropColumn('form_id');
        });
    }
};