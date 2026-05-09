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
        Schema::create('booking_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->unique();
            // Cancellation
            $table->string('cancellation_type')->default('flexible');
            // flexible, moderate, strict, non_refundable
            $table->integer('free_cancel_hours')->default(24);
            $table->decimal('cancellation_fee_percent', 5, 2)->default(0);
            // Reschedule
            $table->boolean('reschedule_allowed')->default(true);
            $table->integer('reschedule_before_hours')->default(24);
            $table->integer('max_reschedules')->default(2);
            // No-show
            $table->decimal('noshow_fee_percent', 5, 2)->default(100);
            // Deposit
            $table->boolean('deposit_required')->default(false);
            $table->decimal('deposit_percent', 5, 2)->default(20);
            $table->integer('deposit_due_days')->default(0); // 0 = at booking
            // Balance
            $table->integer('balance_due_days_before')->default(0); // 0 = at checkin
            $table->text('policy_text')->nullable();
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
        Schema::dropIfExists('booking_policies');
    }
};
