<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $requiredStatuses = [
        'pending',
        'waiting_delivery',
        'shipped',
        'success',
        'canceled',
        'confirmed',
        'completed',
        'cancelled',
        'no_show',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('booking_orders') || !Schema::hasColumn('booking_orders', 'status')) {
            return;
        }

        $existingStatuses = $this->currentEnumValues();
        $statuses = array_values(array_unique(array_merge($existingStatuses, $this->requiredStatuses)));

        $this->setEnumValues($statuses);
    }

    public function down(): void
    {
        if (!Schema::hasTable('booking_orders') || !Schema::hasColumn('booking_orders', 'status')) {
            return;
        }

        $this->setEnumValues([
            'pending',
            'confirmed',
            'cancelled',
            'completed',
            'no_show',
            'success',
        ]);
    }

    private function currentEnumValues(): array
    {
        $database = DB::getDatabaseName();

        $row = DB::selectOne("
            SELECT COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'booking_orders'
              AND COLUMN_NAME = 'status'
        ", [$database]);

        if (empty($row->COLUMN_TYPE) || !preg_match("/^enum\((.*)\)$/", $row->COLUMN_TYPE, $matches)) {
            return [];
        }

        return str_getcsv($matches[1], ',', "'");
    }

    private function setEnumValues(array $statuses): void
    {
        $enumString = implode(',', array_map(
            fn($status) => "'" . str_replace("'", "''", $status) . "'",
            $statuses
        ));

        DB::statement("ALTER TABLE `booking_orders` MODIFY COLUMN `status` ENUM($enumString) NOT NULL DEFAULT 'pending'");
    }
};
