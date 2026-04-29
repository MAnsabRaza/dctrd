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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3)->default('USD');
            $table->string('target_currency', 3);
            $table->decimal('rate', 20, 8);
            $table->string('provider', 50)->nullable()->comment('API provider name');
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamps();

            // Indexes for performance
            $table->index(['base_currency', 'target_currency']);
            $table->index('fetched_at');
            $table->index(['base_currency', 'target_currency', 'fetched_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_rates');
    }
};
