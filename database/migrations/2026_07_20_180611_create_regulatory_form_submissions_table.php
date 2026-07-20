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
                $table->foreignId('role_catalog_id')->constrained('role_catalogs')->onDelete('cascade');
                    $table->foreignId('template_id')->constrained('regulatory_form_templates')->onDelete('cascade');
            $table->unsignedInteger('template_id');
            $table->enum('level', ['primary', 'secondary', 'tertiary', 'quaternary', 'extra1']);
            $table->json('data'); // submitted field values
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

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
