<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;

class EditDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('discounts', function (Blueprint $table) {
            try {
                DB::statement("ALTER TABLE `discounts` DROP COLUMN `name`");
            } catch (\Exception $e) {}
            
            try {
                DB::statement("ALTER TABLE `discount_users` DROP COLUMN `count`");
            } catch (\Exception $e) {}
            
            try {
                DB::statement("ALTER TABLE `discounts` DROP COLUMN `started_at`, MODIFY COLUMN `created_at` int(0) UNSIGNED NOT NULL AFTER `expired_at`;");
            } catch (\Exception $e) {
                // Column might not exist, try without DROP
                try {
                    DB::statement("ALTER TABLE `discounts` MODIFY COLUMN `created_at` int(0) UNSIGNED NOT NULL AFTER `expired_at`;");
                } catch (\Exception $e2) {}
            }

            if (!Schema::hasColumn('discounts', 'title')) {
                $table->string('title')->after('creator_id');
            }
            if (!Schema::hasColumn('discounts', 'code')) {
                $table->string('code', 64)->after('title')->unique();
            }
            if (!Schema::hasColumn('discounts', 'type')) {
                $table->enum('type', ['all_users', 'special_users'])->after('count');
            }
        });
    }
}
