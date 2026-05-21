<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('creator_id')->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->integer('validity_days')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->json('rules')->nullable();
            $table->enum('status', ['draft', 'published', 'inactive'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->integer('sales')->default(0);
            $table->timestamps();

            $table->foreign('creator_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('booking_categories')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_packages');
    }
};
