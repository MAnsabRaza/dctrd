<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'preferred_date_format' => ['type' => 'string', 'length' => 30, 'default' => 'F j, Y', 'after' => 'preferred_area_unit'],
                'preferred_time_format' => ['type' => 'string', 'length' => 30, 'default' => 'g:i a', 'after' => 'preferred_date_format'],
                'preferred_week_start' => ['type' => 'string', 'length' => 10, 'default' => 'Monday', 'after' => 'preferred_time_format'],
                'preferred_speed_unit' => ['type' => 'string', 'length' => 10, 'default' => 'km', 'after' => 'preferred_week_start'],
                'preferred_temperature_unit' => ['type' => 'string', 'length' => 10, 'default' => 'c', 'after' => 'preferred_speed_unit'],
                'preferred_force_unit' => ['type' => 'string', 'length' => 10, 'default' => 'n', 'after' => 'preferred_temperature_unit'],
                'preferred_volume_unit' => ['type' => 'string', 'length' => 10, 'default' => 'l', 'after' => 'preferred_force_unit'],
                'preferred_energy_unit' => ['type' => 'string', 'length' => 10, 'default' => 'btu', 'after' => 'preferred_volume_unit'],
                'preferred_heat_flow_rate_unit' => ['type' => 'string', 'length' => 10, 'default' => 'w', 'after' => 'preferred_energy_unit'],
            ];

            foreach ($columns as $name => $definition) {
                if (!Schema::hasColumn('users', $name)) {
                    $table->string($name, $definition['length'])
                        ->nullable()
                        ->default($definition['default'])
                        ->after($definition['after']);
                }
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'preferred_date_format',
                'preferred_time_format',
                'preferred_week_start',
                'preferred_speed_unit',
                'preferred_temperature_unit',
                'preferred_force_unit',
                'preferred_volume_unit',
                'preferred_energy_unit',
                'preferred_heat_flow_rate_unit',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
