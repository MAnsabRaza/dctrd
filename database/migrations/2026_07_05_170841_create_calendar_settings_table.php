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
        Schema::create('calendar_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('provider', ['google', 'outlook', 'ical']);
            $table->string('event_title_template')->default('{CUSTOMER_NAME} - {PRODUCT_NAME}');
            $table->text('event_description_template')->nullable();
            $table->boolean('add_customer_as_attendee')->default(false);
            $table->boolean('debug_mode')->default(false);
            $table->boolean('ical_export_enabled')->default(false);
            $table->json('sync_status_filter')->nullable();

            // iCal signed URL token
            $table->string('ical_token')->nullable()->unique();

            $table->unique(['user_id', 'provider']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_settings');
    }
};
