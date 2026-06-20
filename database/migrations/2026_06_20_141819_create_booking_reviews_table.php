<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingReviewsTable extends Migration
{
    public function up()
    {
        Schema::create('booking_reviews', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');

            // booking_id instead of product_id
            $table->unsignedBigInteger('booking_id');
            $table->unsignedInteger('creator_id');

            $table->unsignedInteger('product_quality');
            $table->unsignedInteger('purchase_worth');
            $table->unsignedInteger('delivery_quality');
            $table->unsignedInteger('seller_quality');

            $table->char('rates', 10);
            $table->text('description')->nullable();

            $table->unsignedInteger('created_at');
            $table->enum('status', ['pending', 'active']);

            // Foreign Keys
            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_reviews');
    }
}