<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_seasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->string('name');             // Peak, Off-Peak, Holiday
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price_modifier', 10, 4); // 1.5 = +50%, 0.8 = -20%
            $table->string('modifier_type')->default('multiplier'); // multiplier or fixed
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_seasons');
    }
};
