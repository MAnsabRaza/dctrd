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
        Schema::create('calendar_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('rocket_entity_type');
            $table->unsignedInteger('rocket_entity_id');
            $table->unsignedInteger('rocket_event_id')->nullable()->index();
            $table->enum('provider', ['google', 'outlook', 'ical']);
            $table->string('provider_event_id');
            $table->timestamp('last_synced_at')->nullable();

            $table->unique([
                'user_id',
                'rocket_entity_type',
                'rocket_entity_id',
                'provider'
            ], 'calendar_mapping_unique');
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
        Schema::dropIfExists('calendar_mappings');
    }
};
