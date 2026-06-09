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
        Schema::create('checkout_module_translations', function (Blueprint $table) {
                       $table->id();
 
            // FK to checkout_modules
            $table->foreignId('module_id')
                  ->constrained('checkout_modules')
                  ->onDelete('cascade');
 
            // Language locale code
            // Examples: 'en', 'ar', 'fr', 'es', 'de'
            $table->string('locale', 10);
 
            // Label shown on checkout page
            // Example: 'Select Check-in / Check-out Dates'
            $table->string('label', 255);
 
            // Optional helper text below the field
            // Example: 'Minimum 1 night stay required'
            $table->text('help_text')->nullable();
 
            // One translation per module per locale
            $table->unique(['module_id', 'locale']);
 
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
        Schema::dropIfExists('checkout_module_translations');
    }
};
