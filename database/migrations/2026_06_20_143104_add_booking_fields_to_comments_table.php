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
       Schema::table('comments', function (Blueprint $table) {

    $table->unsignedBigInteger('booking_id')
          ->nullable()
          ->after('product_id');

    // FIXED TYPE
    $table->unsignedInteger('booking_review_id')
          ->nullable()
          ->after('product_review_id');

    // Foreign Keys
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropForeign(['booking_review_id']);

            $table->dropColumn(['booking_id', 'booking_review_id']);
        });
    }
};
