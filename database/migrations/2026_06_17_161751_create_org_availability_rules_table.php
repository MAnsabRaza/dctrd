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
        Schema::create('org_availability_rules', function (Blueprint $table) {
             $table->id();
            $table->unsignedInteger('org_id');
            $table->foreign('org_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->enum('availability_mode', [
                'available_by_default',
                'unavailable_by_default'
            ])->default('available_by_default');
            $table->boolean('product_specific_takes_precedence')->default(false);
            $table->boolean('make_all_unavailable_by_default')->default(false);
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
        Schema::dropIfExists('org_availability_rules');
    }
};
