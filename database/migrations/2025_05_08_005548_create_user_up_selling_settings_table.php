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
        Schema::create('user_up_selling_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('enable')->default(true);
            $table->boolean('hide_on_single_product')->default(true);
            $table->boolean('hide_on_cart_page')->default(true);
            $table->boolean('hide_on_checkout_page')->default(true);
            $table->boolean('hide_out_of_stock')->default(true);
            $table->boolean('hide_products_added_cart')->default(true);
            $table->boolean('product_same_category')->default(true);
            $table->json('exclude_products_upsell')->nullable();
            $table->json('exclude_products_upsell_popup')->nullable();
            $table->json('exclude_categories_upsell')->nullable();
            $table->json('exclude_categories_upsell_popup')->nullable();
            $table->unsignedTinyInteger('sort_by')->default(1);
            $table->boolean('show_if_empty')->default(true);
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('user_up_selling_settings');
    }
};
