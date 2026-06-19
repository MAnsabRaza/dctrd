<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('booking_specification_values', function (Blueprint $table) {

            $table->dropForeign(['booking_id']);

            $table->dropIndex(['booking_id']);

            $table->dropColumn('booking_id');
        });
    }

    public function down()
    {
        Schema::table('booking_specification_values', function (Blueprint $table) {

            $table->unsignedBigInteger('booking_id')->after('id');

            $table->index('booking_id');

            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings')
                ->onDelete('cascade');
        });
    }
};