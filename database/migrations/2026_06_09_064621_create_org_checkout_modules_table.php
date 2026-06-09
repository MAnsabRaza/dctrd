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
        Schema::create('org_checkout_modules', function (Blueprint $table) {
                       $table->id();
 
            // Organization or Instructor user ID
            // FK to users table (Rocket LMS users)
            $table->unsignedInteger('org_id');
            $table->foreign('org_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
 
            // Which module this setting is for
            $table->foreignId('module_id')
                  ->constrained('checkout_modules')
                  ->onDelete('cascade');
 
            // Is this module enabled for this org?
            // Default: false (all disabled by default)
            $table->boolean('enabled')->default(false);
 
            // One setting per org per module
            $table->unique(['org_id', 'module_id']);
 
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
        Schema::dropIfExists('org_checkout_modules');
    }
};
