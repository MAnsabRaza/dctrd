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
        Schema::create('erp_credentials', function (Blueprint $table) {
           $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['import_export', 'dropshipping']);

            $table->string('base_url')->nullable();
            $table->string('api_key')->nullable();      // encrypted via mutator
            $table->boolean('is_active')->default(false);

            // "Export Ability" toggle + checklist (Image 2/3)
            $table->boolean('export_ability_enabled')->default(false);
            $table->json('checklist')->nullable();
            // checklist keys: dropship_price, stock_availability, product_approval_status,
            // product_images, shipping_rules, feed_refresh_frequency, tracking_order, tickets_complaints

            // import_dropshipping_enabled toggle mentioned in requirement (Flow 1 & 2 endpoint)
            $table->boolean('import_dropshipping_enabled')->default(false);

            $table->unsignedInteger('rate_limit_per_minute')->default(60);

            $table->timestamp('last_regenerated_at')->nullable();
            $table->timestamps();

            $table->unique(['vendor_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_credentials');
    }
};
