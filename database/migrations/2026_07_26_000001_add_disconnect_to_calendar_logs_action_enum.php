<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('calendar_logs') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `calendar_logs` MODIFY `action` ENUM('create','update','cancel','sync','token_refresh','disconnect') NOT NULL");
    }

    public function down()
    {
        if (!Schema::hasTable('calendar_logs') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `calendar_logs` MODIFY `action` ENUM('create','update','cancel','sync','token_refresh') NOT NULL");
    }
};
