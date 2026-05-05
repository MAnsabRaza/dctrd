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
        Schema::create('booking_resources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->string('name');           // Room 101, Dr. Ahmed, Toyota Corolla
            $table->string('type');           // room, provider, vehicle, asset
            $table->text('description')->nullable();
            $table->integer('capacity')->default(1);
            $table->decimal('extra_price', 10, 2)->default(0);
            $table->json('attributes')->nullable(); // {wifi: true, parking: false, ...}
            $table->string('image')->nullable();
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
        Schema::dropIfExists('booking_resources');
    }
};
