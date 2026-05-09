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
        Schema::create('booking_category_specifications', function (Blueprint $table) {
                $table->id();

            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('specification_id');

            $table->timestamps();

            $table->unique(['category_id', 'specification_id'], 'bcs_cat_spec_unique');


            $table->foreign('category_id')
                ->references('id')
                ->on('booking_categories')
                ->onDelete('cascade');

            $table->foreign('specification_id')
                ->references('id')
                ->on('booking_specifications')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_category_specifications');
    }
};
