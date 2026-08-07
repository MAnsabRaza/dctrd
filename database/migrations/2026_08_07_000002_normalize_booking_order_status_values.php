<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $canonicalStatuses = [
        'pending',
        'confirmed',
        'completed',
        'cancelled',
        'no_show',
    ];

    private array $legacyStatuses = [
        'waiting_delivery',
        'shipped',
        'success',
        'canceled',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('booking_orders') || !Schema::hasColumn('booking_orders', 'status')) {
            return;
        }

        $this->setEnumValues(array_values(array_unique(array_merge(
            $this->canonicalStatuses,
            $this->legacyStatuses
        ))));

        DB::table('booking_orders')->where('status', 'waiting_delivery')->update(['status' => 'confirmed']);
        DB::table('booking_orders')->where('status', 'shipped')->update(['status' => 'completed']);
        DB::table('booking_orders')->where('status', 'success')->update(['status' => 'completed']);
        DB::table('booking_orders')->where('status', 'canceled')->update(['status' => 'cancelled']);

        $this->setEnumValues($this->canonicalStatuses);
    }

    public function down(): void
    {
        if (!Schema::hasTable('booking_orders') || !Schema::hasColumn('booking_orders', 'status')) {
            return;
        }

        $this->setEnumValues(array_values(array_unique(array_merge(
            $this->canonicalStatuses,
            $this->legacyStatuses
        ))));
    }

    private function setEnumValues(array $statuses): void
    {
        $enumString = implode(',', array_map(
            fn ($status) => "'" . str_replace("'", "''", $status) . "'",
            $statuses
        ));

        DB::statement("ALTER TABLE `booking_orders` MODIFY COLUMN `status` ENUM($enumString) NOT NULL DEFAULT 'pending'");
    }
};
