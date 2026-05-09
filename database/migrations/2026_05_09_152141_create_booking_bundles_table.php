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
       Schema::create('booking_bundles', function (Blueprint $table) {
    $table->id();

    // ✅ FIX: same as bookings
    $table->unsignedInteger('creator_id')->index();

    $table->string('title');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('thumbnail')->nullable();
    $table->string('cover')->nullable();
    $table->string('language', 10)->default('en');

    // Pricing
    $table->decimal('price', 12, 2)->default(0);
    $table->decimal('discount_price', 12, 2)->nullable();
    $table->string('currency', 10)->default('USD');
    $table->integer('validity_days')->nullable();

    // Availability
    $table->string('availability_status')->default('medium');
    $table->text('availability_note')->nullable();

    // Status
    $table->enum('status', ['draft','pending','published','rejected','inactive'])->default('draft');
    $table->boolean('featured')->default(false);
    $table->integer('sales')->default(0);
    $table->decimal('rating', 3, 1)->default(0);

    $table->timestamps();

    // ✅ FK (now compatible)
    $table->foreign('creator_id')
        ->references('id')
        ->on('users')
        ->cascadeOnDelete();
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_bundles');
    }
};
