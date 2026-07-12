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
              $table->unsignedBigInteger('resource_id')->nullable()->after('bundle_id');
            $table->date('booking_date')->nullable()->after('resource_id');
            $table->string('start_time')->nullable()->after('booking_date');
            $table->string('end_time')->nullable()->after('start_time');
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
            $table->dropColumn(['resource_id', 'booking_date', 'start_time', 'end_time']);
        });
    }
};
