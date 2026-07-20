<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = ['products', 'webinars', 'bookings', 'bundles'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (!Schema::hasColumn($table, 'allowed_customer_groups')) {
                    // NULL/empty = sab customer groups allowed (no restriction)
                    // e.g. ["importer","wholeseller"] = sirf yeh groups khareed sakte
                    $blueprint->json('allowed_customer_groups')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (Schema::hasColumn($table, 'allowed_customer_groups')) {
                    $blueprint->dropColumn('allowed_customer_groups');
                }
            });
        }
    }
};