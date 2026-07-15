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
            $table->unsignedInteger('vendor_id');
            $table->unsignedBigInteger('vendor_ability_id');
            $table->string('entity'); // customer|product|order|booking|payment
            $table->unsignedBigInteger('local_id')->nullable();
            $table->string('remote_id')->nullable();
            $table->string('sync_hash', 64)->nullable(); // md5 of last-synced payload
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
 
            $table->foreign('vendor_ability_id')
                ->references('id')->on('vendor_abilities')
                ->onDelete('cascade');
 
            $table->unique(['vendor_ability_id', 'entity', 'local_id'], 'erp_map_local_unique');
            $table->index(['vendor_ability_id', 'entity', 'remote_id'], 'erp_map_remote_idx');
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
