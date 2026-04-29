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
        Schema::create('discount_coupon_product', function (Blueprint $table) {
            $table->id();
            // +++++++++++++++ foreign keys ++++++++++++++
            // ===== 1- discount_id =====
            $table->unsignedInteger('discount_id')->nullable();
            $table->foreign('discount_id')->references('id')->on('discounts');
            // ===== 2- product_id =====
            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products');
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
        Schema::dropIfExists('discount_product');
    }
};
