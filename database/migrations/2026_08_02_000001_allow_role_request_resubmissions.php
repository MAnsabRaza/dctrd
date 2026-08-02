<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_role_requests', function (Blueprint $table) {
            $table->dropUnique('user_role_requests_user_id_role_catalog_id_unique');
            $table->index(['user_id', 'role_catalog_id'], 'user_role_requests_user_role_index');
        });
    }

    public function down(): void
    {
        Schema::table('user_role_requests', function (Blueprint $table) {
            $table->dropIndex('user_role_requests_user_role_index');
            $table->unique(['user_id', 'role_catalog_id']);
        });
    }
};
