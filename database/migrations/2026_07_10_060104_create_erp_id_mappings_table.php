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
        Schema::create('erp_id_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('module'); // customer, product, order, booking, payment
            $table->unsignedBigInteger('local_id');
            $table->string('erp_id');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['vendor_id', 'module', 'local_id']);
            $table->index(['module', 'erp_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_id_mappings');
    }
};
