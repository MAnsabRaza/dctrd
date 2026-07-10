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
        Schema::create('ability_field_mappings', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('vendor_ability_id');
            $table->string('entity');
            $table->string('local_field');
            $table->string('remote_field');
            $table->enum('direction', ['import', 'export', 'both'])->default('both');
            $table->timestamps();

            $table->foreign('vendor_ability_id')->references('id')->on('vendor_abilities')->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ability_field_mappings');
    }
};
