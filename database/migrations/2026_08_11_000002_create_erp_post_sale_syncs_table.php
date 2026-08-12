<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_post_sale_syncs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('vendor_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->unsignedInteger('product_id');
            $table->string('invoice_number');
            $table->string('remote_project_id')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['vendor_id', 'order_id', 'invoice_number', 'product_id'], 'erp_post_sale_dedupe');
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_post_sale_syncs');
    }
};
