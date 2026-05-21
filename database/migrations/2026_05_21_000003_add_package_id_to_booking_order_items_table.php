<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('booking_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable()->index()->after('bundle_id');
            $table->foreign('package_id')->references('id')->on('booking_packages')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('booking_order_items', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
        });
    }
};
