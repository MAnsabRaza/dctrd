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
        Schema::create('user_role_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
             $table->foreignId('role_catalog_id')->constrained('role_catalogs')->onDelete('cascade');

            // pending -> user ne submit kiya, admin approve/reject ka intezar
            // active  -> approved, role live hai
            // rejected -> admin ne reject kiya
            $table->enum('status', ['pending', 'active', 'rejected'])->default('pending');

            $table->boolean('is_primary')->default(false); // registration ke waqt wala original role
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable(); // admin user id
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'role_catalog_id']);
            $table->index(['user_id', 'status']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_role_requests');
    }
};
