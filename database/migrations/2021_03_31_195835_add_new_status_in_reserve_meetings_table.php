<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;

class AddNewStatusInReserveMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserve_meetings', function (Blueprint $table) {
            try {
                DB::statement("ALTER TABLE `reserve_meetings` MODIFY COLUMN `status` enum('pending','open','finished','canceled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `password`");
            } catch (\Exception $e) {}

            if (!Schema::hasColumn('reserve_meetings', 'sale_id')) {
                $table->integer('sale_id')->unsigned()->nullable();
            }
            if (!Schema::hasColumn('reserve_meetings', 'date')) {
                $table->integer('date')->unsigned();
            }

            try {
                $table->foreign('sale_id')->on('sales')->references('id')->onDelete('cascade');
            } catch (\Exception $e) {}
        });
    }
}
