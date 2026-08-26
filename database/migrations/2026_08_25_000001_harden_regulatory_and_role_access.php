<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if an index exists on a table (works across MySQL/MariaDB).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $dbName = DB::getDatabaseName();

        $result = DB::select(
            "SELECT COUNT(1) as cnt FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$dbName, $table, $indexName]
        );

        return ($result[0]->cnt ?? 0) > 0;
    }

    public function up(): void
    {
        Schema::table('user_role_requests', function (Blueprint $table) {
            // Drop unique only if it actually exists
            if ($this->indexExists('user_role_requests', 'user_role_requests_user_id_role_catalog_id_unique')) {
                $table->dropUnique('user_role_requests_user_id_role_catalog_id_unique');
            }

            if ($this->indexExists('user_role_requests', 'user_role_requests_user_id_role_catalog_id_index')) {
                $table->dropIndex('user_role_requests_user_id_role_catalog_id_index');
            }

            if (!$this->indexExists('user_role_requests', 'user_role_requests_user_role_idx')) {
                $table->index(['user_id', 'role_catalog_id'], 'user_role_requests_user_role_idx');
            }
        });

        Schema::table('regulatory_form_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('regulatory_form_submissions', 'previous_submission_id')) {
                $table->unsignedBigInteger('previous_submission_id')->nullable()->after('form_submission_id');
                $table->index('previous_submission_id');
            }
        });

        Schema::table('role_catalogs', function (Blueprint $table) {
            if (!Schema::hasColumn('role_catalogs', 'visible_in_registration')) {
                $table->boolean('visible_in_registration')->default(false)->after('active');
            }
            if (!Schema::hasColumn('role_catalogs', 'requires_approval')) {
                $table->boolean('requires_approval')->default(true)->after('visible_in_registration');
            }
        });

        DB::statement("ALTER TABLE user_role_requests MODIFY status ENUM('pending','active','rejected','revoked','deactivated') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE user_role_requests MODIFY status ENUM('pending','active','rejected') NOT NULL DEFAULT 'pending'");

        Schema::table('user_role_requests', function (Blueprint $table) {
            if ($this->indexExists('user_role_requests', 'user_role_requests_user_id_role_catalog_id_index')) {
                $table->dropIndex('user_role_requests_user_id_role_catalog_id_index');
            }
        });

        Schema::table('role_catalogs', function (Blueprint $table) {
            if (Schema::hasColumn('role_catalogs', 'requires_approval')) {
                $table->dropColumn('requires_approval');
            }
            if (Schema::hasColumn('role_catalogs', 'visible_in_registration')) {
                $table->dropColumn('visible_in_registration');
            }
        });

        Schema::table('regulatory_form_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('regulatory_form_submissions', 'previous_submission_id')) {
                $table->dropIndex(['previous_submission_id']);
                $table->dropColumn('previous_submission_id');
            }
        });
    }
};
