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
    Schema::create('erp_id_mappings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
    $table->enum('entity_type', ['customer', 'product', 'order', 'booking', 'payment']);
    $table->unsignedBigInteger('local_id');
    $table->string('remote_id')->nullable();
    $table->timestamp('last_synced_at')->nullable();
    $table->timestamps();

    $table->unique(['vendor_id', 'entity_type', 'local_id'], 'erp_map_unique');
    $table->index(['vendor_id', 'entity_type', 'remote_id']);
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_id_mappings');
    }
};
