<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            if (!Schema::hasColumn('forms', 'connect_regulatory')) {
                $table->boolean('connect_regulatory')->default(false);
            }
            if (!Schema::hasColumn('forms', 'regulatory_role_catalog_id')) {
                $table->unsignedBigInteger('regulatory_role_catalog_id')->nullable();
            }
            if (!Schema::hasColumn('forms', 'regulatory_level')) {
                $table->enum('regulatory_level', ['primary', 'secondary', 'tertiary', 'quaternary', 'extra1'])->nullable();
            }
            if (!Schema::hasColumn('forms', 'regulatory_countries')) {
                $table->json('regulatory_countries')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            if (Schema::hasColumn('forms', 'connect_regulatory')) {
                $table->dropColumn('connect_regulatory');
            }
            if (Schema::hasColumn('forms', 'regulatory_role_catalog_id')) {
                $table->dropColumn('regulatory_role_catalog_id');
            }
            if (Schema::hasColumn('forms', 'regulatory_level')) {
                $table->dropColumn('regulatory_level');
            }
            if (Schema::hasColumn('forms', 'regulatory_countries')) {
                $table->dropColumn('regulatory_countries');
            }
        });
    }
};