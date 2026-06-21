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
        Schema::table('booking_orders', function (Blueprint $table) {
             $table->unsignedBigInteger('bundle_id')->nullable()->after('booking_id');
 
            $table->foreign('bundle_id')->references('id')->on('booking_bundles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_orders', function (Blueprint $table) {
              $table->dropForeign(['bundle_id']);
            $table->dropColumn('bundle_id');
        });
    }
};
