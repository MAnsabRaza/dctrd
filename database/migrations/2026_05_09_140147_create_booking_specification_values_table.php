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
        Schema::create('booking_specification_values', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('specification_id');

            $table->string('value');

            $table->timestamps();

            $table->index('booking_id');
            $table->index('specification_id');

            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings')
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
        Schema::dropIfExists('booking_specification_values');
    }
};
