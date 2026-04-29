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
        Schema::create('cross_selling_relations', function (Blueprint $table) {
            $table->id();
            $table->string('source_type'); // values: 'course', 'product', 'article'
            $table->unsignedBigInteger('source_id');

            $table->string('target_type'); // values: 'course', 'product', 'article' (id)
            $table->unsignedBigInteger('target_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cross_selling_relations');
    }
};
