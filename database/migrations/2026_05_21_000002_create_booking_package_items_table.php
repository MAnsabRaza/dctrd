<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_package_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id')->index();
            $table->unsignedBigInteger('booking_id')->index();
            $table->unsignedBigInteger('resource_id')->nullable()->index();
            $table->integer('quantity')->default(1);
            $table->integer('included_minutes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('rules')->nullable();
            $table->timestamps();

            $table->foreign('package_id')->references('id')->on('booking_packages')->cascadeOnDelete();
            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->foreign('resource_id')->references('id')->on('booking_resources')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_package_items');
    }
};
