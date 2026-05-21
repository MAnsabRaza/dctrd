<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBookingIdToCartTable extends Migration
{
    public function up()
    {
        Schema::table('cart', function (Blueprint $table) {

            $table->unsignedBigInteger('booking_id')
                ->nullable()
                ->after('webinar_id');

            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings')
                ->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::table('cart', function (Blueprint $table) {

            $table->dropForeign(['booking_id']);

            $table->dropColumn('booking_id');

        });
    }
}