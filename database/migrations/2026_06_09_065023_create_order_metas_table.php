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
        Schema::create('order_metas', function (Blueprint $table) {
                        $table->id();
 
            // FK to orders table
            $table->unsignedInteger('order_id');
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');
 
            // Module name / key
            // Examples: 'days', 'hours', 'staff_member',
            //           'persons_children', 'extra_services',
            //           'cancellation_agreed', 'checkout_message',
            //           'reviewer_message'
            $table->string('key', 100);
 
            // JSON encoded value submitted by customer
            $table->text('value')->nullable();
 
            // Fast lookup by order
            $table->index(['order_id', 'key']);

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
        Schema::dropIfExists('order_metas');
    }
};
