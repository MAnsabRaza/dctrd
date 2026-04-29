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
        Schema::create('language_over_ip_popup_settings', function (Blueprint $table) {
            $table->id();
            $table->string('language',255);
            $table->string('notification_title')->nullable();
            $table->string('notification_text')->nullable();
            $table->string('confirm_button_text')->nullable();
            $table->string('cancel_button_text')->nullable();
            $table->boolean('action_type')->default(1); // 1 = show popup, 0 = auto-redirect
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
        Schema::dropIfExists('language_over_ip_popup_settings');
    }
};
