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
        
        Schema::create('booking_specifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('multi_value'); // textbox, multi_value
            $table->json('values')->nullable();             // ["WiFi","Parking","Pool"]
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
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
        Schema::dropIfExists('booking_specifications');
    }
};
