<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds FULLTEXT indexes for fast search on key content tables.
     *
     * NOTE: webinars.title is a plain varchar(64) — NOT a translation table.
     * NOTE: products and bundles use *_translations tables for title/description.
     * NOTE: bookings.title and bookings.description are plain columns.
     */
    public function up(): void
    {
        // ── webinars ─────────────────────────────────────────────────────────
        // Only add if it doesn't already exist (safe re-run)
        if (!$this->indexExists('webinars', 'webinars_search_fulltext')) {
            Schema::table('webinars', function (Blueprint $table) {
                // title VARCHAR(64) and description TEXT are both on this table
                $table->fullText(['title', 'description'])
                      ->name('webinars_search_fulltext');
            });
        }

        // ── product_translations ──────────────────────────────────────────────
        // Products store title/description in the translations table
        if (!$this->indexExists('product_translations', 'product_translations_search_fulltext')) {
            Schema::table('product_translations', function (Blueprint $table) {
                $table->fullText(['title', 'description'])
                      ->name('product_translations_search_fulltext');
            });
        }

        // ── bundle_translations ───────────────────────────────────────────────
        if (!$this->indexExists('bundle_translations', 'bundle_translations_search_fulltext')) {
            Schema::table('bundle_translations', function (Blueprint $table) {
                $table->fullText(['title', 'description'])
                      ->name('bundle_translations_search_fulltext');
            });
        }

        // ── bookings ──────────────────────────────────────────────────────────
        // bookings.title and bookings.description are plain columns
        if (!$this->indexExists('bookings', 'bookings_search_fulltext')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->fullText(['title', 'description'])
                      ->name('bookings_search_fulltext');
            });
        }

        // ── booking_bundles ───────────────────────────────────────────────────
        if (!$this->indexExists('booking_bundles', 'booking_bundles_search_fulltext')) {
            Schema::table('booking_bundles', function (Blueprint $table) {
                $table->fullText(['title', 'description'])
                      ->name('booking_bundles_search_fulltext');
            });
        }

        // ── Regular indexes for common WHERE clauses ──────────────────────────
        if (!$this->indexExists('webinars', 'webinars_status_index')) {
            Schema::table('webinars', function (Blueprint $table) {
                $table->index('status', 'webinars_status_index');
            });
        }

        if (!$this->indexExists('bookings', 'bookings_status_index')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->index('status', 'bookings_status_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->dropFullText('webinars_search_fulltext');
            $table->dropIndex('webinars_status_index');
        });

        Schema::table('product_translations', function (Blueprint $table) {
            $table->dropFullText('product_translations_search_fulltext');
        });

        Schema::table('bundle_translations', function (Blueprint $table) {
            $table->dropFullText('bundle_translations_search_fulltext');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropFullText('bookings_search_fulltext');
            $table->dropIndex('bookings_status_index');
        });

        Schema::table('booking_bundles', function (Blueprint $table) {
            $table->dropFullText('booking_bundles_search_fulltext');
        });
    }

    /**
     * Check if a named index already exists on a table (prevents duplicate-key errors
     * on re-runs or when the index was created manually).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );
        return count($indexes) > 0;
    }
};