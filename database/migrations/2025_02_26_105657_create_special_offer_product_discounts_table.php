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
        Schema::create('special_offer_product_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->unique();
            // ===== 1- creator_id =====
            $table->integer('creator_id')->unsigned();
            $table->foreign('creator_id')->on('users')->references('id')->cascadeOnDelete();
            // ===== 2- product_id =====
            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->on('products')->references('id')->cascadeOnDelete();
            $table->enum('product_type', ['virtual', 'physical'])->nullable();
            $table->enum('status', ['inactive', 'active']);
            $table->integer('percent')->unsigned()->nullable();
            $table->integer('from_date')->unsigned();
            $table->integer('to_date')->unsigned();
            $table->integer('created_at')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_offer_product_discounts');
    }
};
