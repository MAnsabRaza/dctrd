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
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'booking_order_id')) {
                $table->integer('booking_order_id')->unsigned()->nullable()->after('product_order_id');
            }
        });

        // ⚠️ NEECHE WALI LINE ABHI RUN MAT KARNA — pehle neeche ka note parhein
        // DB::statement("ALTER TABLE `sales` MODIFY COLUMN `type` enum('...') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `registration_package_id`");

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'booking_order_id')) {
                $table->integer('booking_order_id')->unsigned()->nullable()->after('product_order_id');
            }
        });

        Schema::table('accounting', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting', 'booking_id')) {
                $table->integer('booking_id')->unsigned()->nullable()->after('product_id');
            }
        });
    }
};
