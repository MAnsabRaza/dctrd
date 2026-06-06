<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that will receive a spatial index on the `location` column.
     * MySQL requires spatial index columns to be NOT NULL.
     */
    private array $spatialTables = ['users', 'webinars', 'products', 'bookings'];

    public function up(): void
    {
        $this->addLocationFields('users', [
            'address_line' => false, // users already have address
            'city'         => true,
            'state'        => true,
            'country'      => true,
            'postal_code'  => true,
            'lat'          => true,
            'lng'          => true,
            'location'     => true,
        ]);

        $this->addLocationFields('webinars');
        $this->addLocationFields('products');

        $this->addLocationFields('bookings', [
            'address_line' => false,
            'city'         => false,
            'state'        => false,
            'country'      => false,
            'postal_code'  => false,
            'lat'          => false,
            'lng'          => false,
            'location'     => true,
        ]);

        $this->addLocationFields('orders', [
            'address_line' => true,
            'city'         => true,
            'state'        => true,
            'country'      => true,
            'postal_code'  => true,
            'lat'          => false,
            'lng'          => false,
            'location'     => false,
        ]);

        if (Schema::hasTable('vendors')) {
            $this->addLocationFields('vendors');
        }
    }

    public function down(): void
    {
        foreach (['orders', 'bookings', 'products', 'webinars', 'users', 'vendors'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                // Drop spatial index before dropping the column
                if (in_array($table, $this->spatialTables, true) && Schema::hasColumn($table, 'location')) {
                    $blueprint->dropSpatialIndex(['location']);
                }

                foreach ($this->columnsForDrop($table) as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $blueprint->dropColumn($column);
                    }
                }
            });
        }
    }

    private function addLocationFields(string $table, array $columns = []): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $columns = array_merge([
            'address_line' => true,
            'city'         => true,
            'state'        => true,
            'country'      => true,
            'postal_code'  => true,
            'lat'          => true,
            'lng'          => true,
            'location'     => true,
        ], $columns);

        $needsSpatialIndex = $columns['location'] && in_array($table, $this->spatialTables, true);

        // ── Step 1: add any missing columns ─────────────────────────────────
        Schema::table($table, function (Blueprint $blueprint) use ($table, $columns, $needsSpatialIndex) {
            if (in_array($table, ['webinars', 'products'], true) && !Schema::hasColumn($table, 'location_enabled')) {
                $blueprint->boolean('location_enabled')->default(false);
            }

            if ($columns['address_line'] && !Schema::hasColumn($table, 'address_line')) {
                $blueprint->string('address_line', 255)->nullable();
            }

            if ($columns['city'] && !Schema::hasColumn($table, 'city')) {
                $blueprint->string('city', 100)->nullable();
            }

            if ($columns['state'] && !Schema::hasColumn($table, 'state')) {
                $blueprint->string('state', 100)->nullable();
            }

            if ($columns['country'] && !Schema::hasColumn($table, 'country')) {
                $blueprint->string('country', 100)->nullable();
            }

            if ($columns['postal_code'] && !Schema::hasColumn($table, 'postal_code')) {
                $blueprint->string('postal_code', 20)->nullable();
            }

            if ($columns['lat'] && !Schema::hasColumn($table, 'lat')) {
                $blueprint->decimal('lat', 10, 8)->nullable();
            }

            if ($columns['lng'] && !Schema::hasColumn($table, 'lng')) {
                $blueprint->decimal('lng', 11, 8)->nullable();
            }

            if ($columns['location'] && !Schema::hasColumn($table, 'location')) {
                if ($needsSpatialIndex) {
                    // Spatial index requires NOT NULL — add with default POINT(0 0)
                    $blueprint->point('location')
                        ->default(DB::raw("ST_GeomFromText('POINT(0 0)')"))
                        ->nullable(false);
                } else {
                    $blueprint->point('location')->nullable();
                }
            }
        });

        // ── Step 2: if column already existed as nullable, convert it ────────
        // This handles cases where a previous (failed) migration already added
        // the column as nullable. We must make it NOT NULL before adding the
        // spatial index, otherwise MySQL throws error 1252.
        if ($needsSpatialIndex && Schema::hasColumn($table, 'location')) {
            // First set any NULL values to the default point so NOT NULL won't fail
            DB::statement("UPDATE `{$table}` SET `location` = ST_GeomFromText('POINT(0 0)') WHERE `location` IS NULL");

            // Alter the column to NOT NULL with a default
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `location` POINT NOT NULL DEFAULT (ST_GeomFromText('POINT(0 0)'))");
        }

        // ── Step 3: add spatial index (separate call, after column is ready) ─
        if ($needsSpatialIndex) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                // Only add if the index does not already exist
                $indexes = collect(DB::select("SHOW INDEX FROM `{$table}`"))
                    ->pluck('Key_name')
                    ->toArray();

                $indexName = "{$table}_location_spatialindex";

                if (!in_array($indexName, $indexes, true)) {
                    $blueprint->spatialIndex('location');
                }
            });
        }
    }

    private function columnsForDrop(string $table): array
    {
        if ($table === 'users') {
            return ['city', 'state', 'country', 'postal_code', 'lat', 'lng', 'location'];
        }

        if ($table === 'bookings') {
            return ['location'];
        }

        if ($table === 'orders') {
            return ['address_line', 'city', 'state', 'country', 'postal_code'];
        }

        return ['location_enabled', 'address_line', 'city', 'state', 'country', 'postal_code', 'lat', 'lng', 'location'];
    }
};