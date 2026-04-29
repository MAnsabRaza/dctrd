<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAffiliateColumnToAccountingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting', 'referred_user_id')) {
                $table->integer('referred_user_id')->unsigned()->nullable();
            }
            if (!Schema::hasColumn('accounting', 'is_affiliate_amount')) {
                $table->boolean('is_affiliate_amount')->default(false);
            }
            if (!Schema::hasColumn('accounting', 'is_affiliate_commission')) {
                $table->boolean('is_affiliate_commission')->default(false);
            }
        });
    }
}
