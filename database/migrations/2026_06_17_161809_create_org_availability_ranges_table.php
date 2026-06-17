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
        Schema::create('org_availability_ranges', function (Blueprint $table) {
              $table->id();
            $table->unsignedInteger('org_id');
            $table->foreign('org_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            // Values: 'custom', 'daily', 'weekly', 'monthly', 'date_range'
            $table->string('range_type', 50);
            $table->date('from_date');
            $table->date('to_date');
            $table->boolean('bookable')->default(true);
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
        Schema::dropIfExists('org_availability_ranges');
    }
};
