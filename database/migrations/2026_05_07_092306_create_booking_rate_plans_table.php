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
        Schema::create('booking_rate_plans', function (Blueprint $table) {
              $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->string('name');
            $table->string('type'); // base, seasonal, dow, promo, pax
            $table->decimal('price', 12, 2);
            $table->string('price_unit')->default('day'); // day, hour, night, person
            $table->string('calculation_type')->default('fixed'); // fixed, percent_off, percent_of_base
            $table->json('conditions')->nullable();
            // conditions examples:
            // {"days_of_week": [1,2,3,4,5]} for weekday
            // {"min_persons": 2, "max_persons": 5} for pax
            // {"min_nights": 3} for long-stay
            $table->integer('priority')->default(0); // higher = applied last
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
        Schema::dropIfExists('booking_rate_plans');
    }
};
