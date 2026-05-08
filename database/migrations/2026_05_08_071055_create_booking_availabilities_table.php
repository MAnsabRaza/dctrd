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
        Schema::create('booking_availabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->unsignedBigInteger('resource_id')->nullable()->index();
            $table->date('date')->index();
            $table->boolean('is_available')->default(true);
            $table->integer('slots_available')->nullable(); 
            $table->decimal('price_override', 12, 2)->nullable();
            $table->string('close_reason')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'resource_id', 'date']);
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('resource_id')->references('id')->on('booking_resources')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_availabilities');
    }
};
