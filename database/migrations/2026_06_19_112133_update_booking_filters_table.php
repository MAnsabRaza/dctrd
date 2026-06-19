<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('booking_filters', function (Blueprint $table) {

            $table->dropColumn([
                'type',
                'options',
                'is_required',
            ]);

            $table->string('language', 10)
                ->default('en')
                ->after('title');
        });

        // Create booking_filter_options table
        Schema::create('booking_filter_options', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('filter_id');

            $table->string('name');

            $table->timestamps();

            $table->softDeletes();

            $table->foreign('filter_id')
                ->references('id')
                ->on('booking_filters')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_filter_options');

        Schema::table('booking_filters', function (Blueprint $table) {

            $table->string('type')->default('checkbox');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);

            $table->dropColumn('language');
        });
    }
};