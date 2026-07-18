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
        Schema::create('erp_sync_logs', function (Blueprint $table) {
             $table->id();
          $table->unsignedInteger('vendor_id');
$table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('entity_type', ['customer', 'product', 'order', 'booking', 'payment']);
            $table->unsignedBigInteger('local_id');
            $table->string('remote_id')->nullable();

            $table->enum('action', ['create', 'update', 'delete'])->default('create');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');

            $table->unsignedTinyInteger('attempts')->default(0);
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['vendor_id', 'entity_type', 'local_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_sync_logs');
    }
};
