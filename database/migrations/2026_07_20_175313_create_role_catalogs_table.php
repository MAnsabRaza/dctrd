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
        Schema::create('role_catalogs', function (Blueprint $table) {
          $table->id();
            $table->string('family');      // instructor | organization | customer
            $table->string('key')->unique(); // e.g. 'seller', 'operator', 'importer'
            $table->string('label');       // Display name, e.g. "Tour Operator"
            $table->json('supersedes')->nullable(); // array of 'key' values this role already covers
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('family');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_catalogs');
    }
};
