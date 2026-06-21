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
        Schema::create('booking_faqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('creator_id');
            $table->unsignedBigInteger('booking_id');
            
            $table->string('locale', 191)->index();
            $table->string('title');
            $table->text('answer');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
       
             $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
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
        Schema::dropIfExists('booking_faqs');
    }
};
