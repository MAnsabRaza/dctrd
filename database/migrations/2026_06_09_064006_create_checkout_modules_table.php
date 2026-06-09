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
        Schema::create('checkout_modules', function (Blueprint $table) {
                       $table->id();
 
            // Module unique name/key
            // Values: 'days', 'hours', 'staff_member',
            //         'persons_children', 'extra_services',
            //         'cancellation_policy', 'checkout_message',
            //         'reviewer_message'
            $table->string('name', 100)->unique();
 
            // Input type for frontend rendering
            // Values: 'date_range', 'time_slot', 'select',
            //         'stepper', 'checkbox_list',
            //         'info_checkbox', 'textarea'
            $table->string('input_type', 50);
 
            // JSON config for module
            // Example for 'extra_services':
            // {"options": [
            //   {"label": "Breakfast", "price": 10},
            //   {"label": "Transfer",  "price": 20}
            // ]}
            // Example for 'stepper':
            // {"adults":{"min":1,"max":20},
            //  "children":{"min":0,"max":10},
            //  "rooms":{"min":1,"max":10}}
            $table->json('config')->nullable();
 
            // Pricing rule for this module
            // Examples:
            // {"type": "per_day"}
            // {"type": "per_hour"}
            // {"type": "per_person", "amount": 15}
            // {"type": "additive"}
            // {"type": "none"}
            $table->json('price_rule')->nullable();
 
            // Display order on checkout page
            $table->integer('order_index')->default(0);
 
            // Is this module available for orgs to use?
            $table->boolean('is_active')->default(true);
 
            // Is this module required when enabled?
            $table->boolean('is_required')->default(false);
 
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
        Schema::dropIfExists('checkout_modules');
    }
};
