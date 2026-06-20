<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {

            if (!Schema::hasColumn('comments', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('product_id');
            }

            if (!Schema::hasColumn('comments', 'booking_review_id')) {
                $table->unsignedInteger('booking_review_id')->nullable()->after('product_review_id');
            }

            // add FK only if columns exist
            if (Schema::hasColumn('comments', 'booking_id')) {
                $table->foreign('booking_id')
                      ->references('id')
                      ->on('bookings')
                      ->onDelete('cascade');
            }

            if (Schema::hasColumn('comments', 'booking_review_id')) {
                $table->foreign('booking_review_id')
                      ->references('id')
                      ->on('booking_reviews')
                      ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {

            if (Schema::hasColumn('comments', 'booking_id')) {
                $table->dropForeign(['booking_id']);
                $table->dropColumn('booking_id');
            }

            if (Schema::hasColumn('comments', 'booking_review_id')) {
                $table->dropForeign(['booking_review_id']);
                $table->dropColumn('booking_review_id');
            }
        });
    }
};