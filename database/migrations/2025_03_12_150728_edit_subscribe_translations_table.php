<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // MariaDB doesn't support renameColumn, use CHANGE instead
        DB::statement("ALTER TABLE `subscribe_translations` CHANGE `description` `subtitle` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");

        Schema::table('subscribe_translations', function (Blueprint $table) {
            $table->text('description')->nullable();
        });
    }

};
