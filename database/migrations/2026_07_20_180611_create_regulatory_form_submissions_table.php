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
        Schema::create('regulatory_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('role_catalog_id');
            $table->unsignedInteger('template_id');
            $table->enum('level', ['primary', 'secondary', 'tertiary', 'quaternary', 'extra1']);
            $table->json('data'); // submitted field values
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_catalog_id')->references('id')->on('role_catalog')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('regulatory_form_templates')->onDelete('cascade');

            $table->index(['user_id', 'role_catalog_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regulatory_form_submissions');
    }
};
