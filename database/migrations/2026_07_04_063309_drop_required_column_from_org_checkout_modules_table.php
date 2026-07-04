<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('org_checkout_modules', function (Blueprint $table) {
            if (Schema::hasColumn('org_checkout_modules', 'required')) {
                $table->dropColumn('required');
            }
        });
    }

    public function down(): void
    {
        Schema::table('org_checkout_modules', function (Blueprint $table) {
            $table->boolean('required')->default(false);
        });
    }
};