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
        Schema::create('entity_checkout_modules', function (Blueprint $table) {
                        $table->id();
 
            // Type of entity this override applies to
            // Values: 'product', 'course', 'booking', 'bundle',
            //         'product_bundle', 'booking_bundle'
            $table->string('entity_type', 50);
 
            // ID of the specific product/course/booking/bundle
            $table->unsignedBigInteger('entity_id');
 
            // Which module is being overridden
            $table->foreignId('module_id')
                  ->constrained('checkout_modules')
                  ->onDelete('cascade');
 
            // Override enabled status
            $table->boolean('enabled')->default(false);
 
            // Optional: override config for this specific entity
            // Example: different time slots for this specific booking
            // {"slots": ["10:00", "11:00", "14:00"]}
            $table->json('config_override')->nullable();
 
            // One override per entity per module
            $table->unique(
                ['entity_type', 'entity_id', 'module_id'],
                'entity_module_unique'
            );
 
            // Index for fast lookups
            $table->index(['entity_type', 'entity_id']);

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
        Schema::dropIfExists('entity_checkout_modules');
    }
};
