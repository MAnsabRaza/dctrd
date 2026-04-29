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
        Schema::create('language_over_ip_country_mapping_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('country_id'); // References Region table (country ID)
            // ======== Foreign key : country_id ========
            $table->foreign('country_id')->references('country_id')->on('regions')->onDelete('cascade');
            $table->string('language', 255);  // Language (e.g., English, Arabic)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('language_over_ip_country_mapping_settings');
    }
};
