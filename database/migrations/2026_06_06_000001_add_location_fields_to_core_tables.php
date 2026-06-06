<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $spatialTables = ['users', 'webinars', 'products', 'bookings'];

    public function up(): void
    {
        $this->addLocationFields('users', [
            'address_line' => false, // users already have address
            'city' => true,
            'state' => true,
            'country' => true,
            'postal_code' => true,
            'lat' => true,
            'lng' => true,
            'location' => true,
        ]);

        $this->addLocationFields('webinars');
        $this->addLocationFields('products');

        $this->addLocationFields('bookings', [
            'address_line' => false,
            'city' => false,
            'state' => false,
            'country' => false,
            'postal_code' => false,
            'lat' => false,
            'lng' => false,
            'location' => true,
        ]);

        $this->addLocationFields('orders', [
            'address_line' => true,
            'city' => true,
            'state' => true,
            'country' => true,
            'postal_code' => true,
            'lat' => false,
            'lng' => false,
            'location' => false,
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
            'city' => true,
            'state' => true,
            'country' => true,
            'postal_code' => true,
            'lat' => true,
            'lng' => true,
            'location' => true,
        ], $columns);

        Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
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
                $blueprint->point('location')->nullable();
            }
        });

        if ($columns['location'] && in_array($table, $this->spatialTables, true)) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (Schema::hasColumn($table, 'location')) {
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
