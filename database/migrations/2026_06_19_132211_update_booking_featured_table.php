<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('booking_featured', function (Blueprint $table) {

            // Foreign Key remove first
            $table->dropForeign(['category_id']);

            // Remove old columns
            $table->dropColumn([
                'category_id',
                'placement',
                'starts_at',
                'expires_at',
                'order',
            ]);

            // Change status field
            $table->dropColumn('status');

            // New fields
            $table->string('language', 10)->default('en')->after('booking_id');

            $table->unsignedInteger('user_id')->nullable()->after('language');

            $table->string('title')->after('user_id');

            $table->enum('status', [
                'pending',
                'published'
            ])->default('pending')->after('title');

            $table->enum('page', [
                'home_categories',
                'home',
                'categories'
            ])->default('home')->after('status');

            $table->text('description')->nullable()->after('page');

            // Foreign Key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('booking_featured', function (Blueprint $table) {

            $table->dropForeign(['user_id']);

            $table->dropColumn([
                'language',
                'user_id',
                'title',
                'status',
                'page',
                'description',
            ]);

            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('placement')->default('home')->index();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('expires_at')->nullable()->index();
            $table->integer('order')->default(0)->index();
            $table->boolean('status')->default(true)->index();

            $table->foreign('category_id')
                ->references('id')
                ->on('booking_categories')
                ->cascadeOnDelete();
        });
    }
};