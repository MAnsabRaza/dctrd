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
        Schema::create('calendar_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->enum('provider', ['google', 'outlook', 'ical']);
            $table->enum('action', [
                'create',
                'update',
                'cancel',
                'sync',
                'token_refresh',
                'disconnect'
            ]);

            $table->enum('status', ['success', 'failed', 'pending']);

            $table->unsignedInteger('booking_order_id')
                ->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();
            $table->index(['user_id', 'provider', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_logs');
    }
};
