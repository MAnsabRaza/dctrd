<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if columns don't already exist
            if (!Schema::hasColumn('users', 'preferred_currency')) {
                $table->string('preferred_currency', 3)->default('USD')->after('timezone')->nullable();
            }
            if (!Schema::hasColumn('users', 'preferred_length_unit')) {
                $table->string('preferred_length_unit', 10)->default('km')->after('preferred_currency')->nullable();
            }
            if (!Schema::hasColumn('users', 'preferred_mass_unit')) {
                $table->string('preferred_mass_unit', 10)->default('kg')->after('preferred_length_unit')->nullable();
            }
            if (!Schema::hasColumn('users', 'preferred_area_unit')) {
                $table->string('preferred_area_unit', 10)->default('sqm')->after('preferred_mass_unit')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['preferred_currency', 'preferred_length_unit', 'preferred_mass_unit', 'preferred_area_unit'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
