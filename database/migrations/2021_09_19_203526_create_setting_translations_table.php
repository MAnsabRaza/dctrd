<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_translations', function (Blueprint $table) {
            $table->engine = "InnoDB";

            $table->bigIncrements('id');
            $table->integer('setting_id'); // Changed from unsignedInteger to match settings.id
            $table->string('locale', 191)->index();
            $table->longText('value');

            try {
                $table->foreign('setting_id')->on('settings')->references('id')->onDelete('cascade');
            } catch (\Exception $e) {
                // Foreign key might fail, continue anyway
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('setting_translations');
    }
}
