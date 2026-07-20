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
        Schema::create('regulatory_form_templates', function (Blueprint $table) {
             $table->id();
            $table->unsignedInteger('role_catalog_id');
            $table->enum('level', ['primary', 'secondary', 'tertiary', 'quaternary', 'extra1']);
            $table->string('label'); // e.g. "Company Information", "Branch Information"
            $table->json('fields'); // field definitions: [{key,label,type,required}, ...]
            $table->json('countries')->nullable(); // agar specific countries ke liye alag rules
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('role_catalog_id')->references('id')->on('role_catalog')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regulatory_form_templates');
    }
};
