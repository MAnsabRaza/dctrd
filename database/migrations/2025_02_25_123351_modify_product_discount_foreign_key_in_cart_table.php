<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('cart', function (Blueprint $table) {
              // Drop the existing foreign key if it exists
              $table->dropForeign(['product_discount_id']);

              // Add the new foreign key constraint pointing to the discounts table
              $table->foreign('product_discount_id')->references('id')->on('discounts')->onDelete('set null');
          });
    
    }


    public function down()
    {
        Schema::table('cart', function (Blueprint $table) {
            $table->dropForeign(['product_discount_id']);
        });
    }
};
