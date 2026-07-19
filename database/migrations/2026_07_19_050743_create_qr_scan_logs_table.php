<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->string('short_code')->index();

            // Polymorphic link back to Product / Course / Booking / Bundle
            $table->string('item_type')->nullable();
            $table->unsignedInteger('item_id')->nullable();

            $table->unsignedInteger('user_id')->nullable(); // agar scan karne wala logged-in ho
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();

            $table->boolean('is_checkin')->default(false);
            $table->timestamp('checked_in_at')->nullable();

            $table->timestamps();

            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_scan_logs');
    }
};
