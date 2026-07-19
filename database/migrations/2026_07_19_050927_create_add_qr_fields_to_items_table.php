<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * IMPORTANT: '$tables' array mein apne actual Rocket LMS table names confirm/adjust karo.
 * Yahan assume kiya gaya hai: products, courses, bookings, bundles.
 * Agar koi table exist nahi karti to woh automatically skip ho jayegi (safe).
 */
return new class extends Migration
{
    protected array $tables = [
        'products',
        'courses',
        'bookings',
        'bundles',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (!Schema::hasColumn($table, 'qr_enabled')) {
                    $blueprint->boolean('qr_enabled')->default(false);
                }
                if (!Schema::hasColumn($table, 'short_url')) {
                    $blueprint->string('short_url')->nullable();
                }
                if (!Schema::hasColumn($table, 'short_code')) {
                    $blueprint->string('short_code')->nullable()->index();
                }
                if (!Schema::hasColumn($table, 'qr_image_path')) {
                    $blueprint->string('qr_image_path')->nullable();
                }
                if (!Schema::hasColumn($table, 'qr_last_refreshed_at')) {
                    $blueprint->timestamp('qr_last_refreshed_at')->nullable();
                }
                if (!Schema::hasColumn($table, 'qr_revoked_at')) {
                    $blueprint->timestamp('qr_revoked_at')->nullable();
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
                $cols = ['qr_enabled', 'short_url', 'short_code', 'qr_image_path', 'qr_last_refreshed_at', 'qr_revoked_at'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn($table, $col)) {
                        $blueprint->dropColumn($col);
                    }
                }
            });
        }
    }
};
