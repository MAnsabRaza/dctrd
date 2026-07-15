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
        Schema::create('erp_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_ability_id');
            $table->string('event_type'); // e.g. product.updated, client.synced
            $table->longText('payload')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
 
            $table->foreign('vendor_ability_id')
                ->references('id')->on('vendor_abilities')
                ->onDelete('cascade');
 
            $table->index(['vendor_ability_id', 'processed']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_webhook_events');
    }
};
