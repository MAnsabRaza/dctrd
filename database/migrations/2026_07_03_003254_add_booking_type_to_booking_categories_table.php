<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('booking_categories', function (Blueprint $table) {
            $table->string('booking_type')->nullable()->after('parent_id')->index();
            $table->string('template_key')->nullable()->after('booking_type')->index();
        });
    }

    public function down()
    {
        Schema::table('booking_categories', function (Blueprint $table) {
            $table->dropColumn('booking_type');
             $table->dropColumn('template_key');
        });
    }
};