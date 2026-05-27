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
       Schema::create('booking_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedInteger('customer_id')->index();
            $table->text('comment');
            $table->unsignedTinyInteger('rating');           // 1-5
            $table->unsignedTinyInteger('value_rating')->nullable();
            $table->unsignedTinyInteger('delivery_rating')->nullable();
            $table->unsignedTinyInteger('seller_rating')->nullable();
            $table->enum('status', ['pending', 'active', 'rejected'])->default('pending');
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'customer_id']);
            $table->foreign('order_id')->references('id')->on('booking_orders')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_reviews');
    }
};
