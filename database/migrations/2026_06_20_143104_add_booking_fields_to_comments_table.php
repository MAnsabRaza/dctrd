<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {

            // booking_id (depends on bookings table type - usually BIGINT, so keep BIGINT safe)
            $table->unsignedBigInteger('booking_id')
                  ->nullable()
                  ->after('product_id');

            // FIX: booking_reviews.id is INT UNSIGNED
            $table->unsignedInteger('booking_review_id')
                  ->nullable()
                  ->after('product_review_id');

            $table->foreign('booking_id')
                  ->references('id')
                  ->on('bookings')
                  ->onDelete('cascade');

            $table->foreign('booking_review_id')
                  ->references('id')
                  ->on('booking_reviews')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {

            $table->dropForeign(['booking_id']);
            $table->dropForeign(['booking_review_id']);

            $table->dropColumn(['booking_id', 'booking_review_id']);
        });
    }
};