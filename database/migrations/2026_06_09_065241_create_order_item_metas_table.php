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
        Schema::create('order_item_metas', function (Blueprint $table) {
                       $table->id();
 
            // FK to order_items table
            $table->unsignedInteger('order_item_id');
            $table->foreign('order_item_id')
                  ->references('id')
                  ->on('order_items')
                  ->onDelete('cascade');
 
            // Module name / key
            $table->string('key', 100);
 
            // JSON encoded submitted value
            $table->text('value')->nullable();
 
            // Fast lookup
            $table->index(['order_item_id', 'key']);
 
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
        Schema::dropIfExists('order_item_metas');
    }
};
