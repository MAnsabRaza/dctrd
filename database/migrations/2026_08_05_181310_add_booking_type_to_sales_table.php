<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->modifyEnum('booking', add: true);
    }

    public function down()
    {
        $this->modifyEnum('booking', add: false);
    }

    private function modifyEnum(string $value, bool $add)
    {
        $database = DB::getDatabaseName();

        $row = DB::selectOne("
            SELECT COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = 'sales' 
            AND COLUMN_NAME = 'type'
        ", [$database]);

        // Example COLUMN_TYPE: enum('webinar','meeting','subscribe',...)
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

        DB::statement("ALTER TABLE `sales` MODIFY COLUMN `type` ENUM($enumString) NOT NULL");
    }
};