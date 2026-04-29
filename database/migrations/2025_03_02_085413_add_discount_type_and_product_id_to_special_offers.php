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
        Schema::table('special_offers', function (Blueprint $table) {
            $table->enum('discount_type', ['course', 'product'])->after('percent')->nullable();
            // +++++++++++++ foreign key : product_id +++++++++++++
            if (!Schema::hasColumn('special_offers', 'product_id')) {
                $table->unsignedInteger('product_id')->after('discount_type')->nullable();
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('special_offers', function (Blueprint $table) {
            //
        });
    }
};
