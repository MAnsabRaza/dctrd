<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $this->modifyEnum('success', add: true);
    }

    public function down()
    {
        $this->modifyEnum('success', add: false);
    }

    private function modifyEnum(string $value, bool $add)
    {
        $database = DB::getDatabaseName();

        $row = DB::selectOne("
            SELECT COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = 'booking_orders' 
            AND COLUMN_NAME = 'status'
        ", [$database]);

        preg_match("/^enum\((.*)\)$/", $row->COLUMN_TYPE, $matches);

        $existingValues = str_getcsv($matches[1], ',', "'");

        if ($add) {
            if (!in_array($value, $existingValues)) {
                $existingValues[] = $value;
            }
        } else {
            $existingValues = array_filter($existingValues, fn($v) => $v !== $value);
        }

        $enumString = implode(',', array_map(fn($v) => "'" . addslashes($v) . "'", $existingValues));

        DB::statement("ALTER TABLE `booking_orders` MODIFY COLUMN `status` ENUM($enumString) NOT NULL DEFAULT 'pending'");
    }
};