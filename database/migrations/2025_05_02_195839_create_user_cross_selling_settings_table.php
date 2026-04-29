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
        Schema::create('user_cross_selling_settings', function (Blueprint $table) {
            $table->id();
            
            $table->boolean('enable')->default(true); //Cross Selling is enable or not
            $table->boolean('hide_on_single_product')->default(true); //Cross Selling is show in single product page
            $table->unsignedTinyInteger('display_on')->default(1); //1 for pop up - 2 for Below Add to cart button - 3 for Above Description Tab - 4 for Below description
            $table->boolean('display_type_on_single_product')->default(true)->comment('Only visible if display_on == 2'); // display as slider in Below Add to cart button condition only
            $table->boolean('show_on_cart_page')->default(true); //Cross Selling is show in cart page
            $table->unsignedTinyInteger('product_bundle_type_cart')->default(1); //Cross Selling is show in cart page order by 1 for The largest quantity in order 2 for random 3 for The most expensive
            $table->unsignedTinyInteger('display_on_cart')->default(1); //1 for pop up - 2 for Below Add to cart button - 3 for Above Description Tab - 4 for Below description
            $table->boolean('show_on_checkout_page')->default(true); //Cross Selling is show in checkout page
            $table->unsignedTinyInteger('product_bundle_type_checkout')->default(1); //Cross Selling is show in cart page order by 1 for The largest quantity in order 2 for random 3 for The most expensive
            $table->unsignedTinyInteger('display_on_checkout')->default(1); //1 for pop up - 2 for Below Add to cart button - 3 for Above Description Tab - 4 for Below description
            $table->boolean('same_bundle_in_cart')->default(true); // The same bundle can display in cart page and checkout page.
            $table->text('description')->nullable(); // cross selling description
            $table->unsignedTinyInteger('display_saved_price')->default(1); // display price
            $table->boolean('override_products')->default(true); //Remove the same products on cart when add combo.
            $table->boolean('hide_out_of_stock')->default(true); // Do not show crosssell if one of bundle items is out of stock
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
        Schema::dropIfExists('user_cross_selling_settings');
    }
};
