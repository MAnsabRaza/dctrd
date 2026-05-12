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
        Schema::create('booking_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('item_type'); // booking or bundle
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->unsignedBigInteger('bundle_id')->nullable()->index();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->date('booking_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('persons')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->json('selected_variants')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('booking_orders')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
            $table->foreign('resource_id')->references('id')->on('booking_resources')->onDelete('set null');
            $table->foreign('bundle_id')->references('id')->on('booking_bundles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_order_items');
    }
};
