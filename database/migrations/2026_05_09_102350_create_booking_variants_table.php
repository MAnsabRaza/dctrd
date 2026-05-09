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
        Schema::create('booking_variants', function (Blueprint $table) {
              $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->string('name');        // Language, Mode, Tour Type
            $table->json('options');       // ["English","Arabic","Turkish"]
            $table->decimal('price_modifier', 10, 2)->default(0);
            $table->boolean('affects_availability')->default(false);
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
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
        Schema::dropIfExists('booking_variants');
    }
};
