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
        Schema::create('booking_comments', function (Blueprint $table) {
             $table->id();
            $table->unsignedInteger('user_id')->index();
             $table->unsignedBigInteger('booking_id')->index();
             $table->text('comment');

    $table->boolean('is_active')->default(true);
             $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
             $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_comments');
    }
};
