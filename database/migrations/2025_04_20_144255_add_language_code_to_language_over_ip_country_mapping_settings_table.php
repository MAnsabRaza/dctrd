<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('language_over_ip_country_mapping_settings', function (Blueprint $table) 
        {
            // language_code (e.g., en, ar)
            $table->string('language_code')->nullable()->after('language');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('language_over_ip_country_mapping_settings', function (Blueprint $table) {
            $table->dropColumn('language_code');
        });
    }
};
