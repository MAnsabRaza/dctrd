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
        Schema::create('vendor_abilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('vendor_id');
            $table->unsignedBigInteger('ability_id');
            $table->json('config_json')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->enum('sync_status', ['idle', 'running', 'failed', 'success'])->default('idle');
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ability_id')->references('id')->on('abilities')->onDelete('cascade');
            $table->unique(['vendor_id', 'ability_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendor_abilities');
    }
};
