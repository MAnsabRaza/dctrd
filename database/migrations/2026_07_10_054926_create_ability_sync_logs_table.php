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
        Schema::create('ability_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_ability_id');
            $table->string('entity');
            $table->unsignedBigInteger('local_id')->nullable();
            $table->string('remote_id')->nullable();
            $table->enum('status', ['success', 'failed', 'retrying']);
            $table->longText('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('vendor_ability_id')->references('id')->on('vendor_abilities')->onDelete('cascade');
            $table->index(['vendor_ability_id', 'entity']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ability_sync_logs');
    }
};
