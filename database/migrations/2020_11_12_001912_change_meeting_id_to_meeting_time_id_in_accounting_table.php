<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMeetingIdToMeetingTimeIdInAccountingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting', function (Blueprint $table) {
            try {
                DB::statement("ALTER TABLE `accounting` DROP FOREIGN KEY `accounting_meeting_id_foreign`;");
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
            DB::statement("ALTER TABLE `accounting` CHANGE COLUMN  `meeting_id` `meeting_time_id` INTEGER UNSIGNED NULL");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting', function (Blueprint $table) {
            //
        });
    }
}
