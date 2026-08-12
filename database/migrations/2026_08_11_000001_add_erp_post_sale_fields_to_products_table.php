<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'erp_post_sale_enabled')) {
                $table->boolean('erp_post_sale_enabled')->default(false)->after('reviewer_message');
            }

            if (!Schema::hasColumn('products', 'erp_category_id')) {
                $table->string('erp_category_id')->nullable()->after('erp_post_sale_enabled');
            }

            if (!Schema::hasColumn('products', 'erp_category_name')) {
                $table->string('erp_category_name')->nullable()->after('erp_category_id');
            }

            if (!Schema::hasColumn('products', 'erp_subcategory_id')) {
                $table->string('erp_subcategory_id')->nullable()->after('erp_category_name');
            }

            if (!Schema::hasColumn('products', 'erp_subcategory_name')) {
                $table->string('erp_subcategory_name')->nullable()->after('erp_subcategory_id');
            }

            if (!Schema::hasColumn('products', 'erp_staff_ids')) {
                $table->json('erp_staff_ids')->nullable()->after('erp_subcategory_name');
            }

            if (!Schema::hasColumn('products', 'erp_task_templates')) {
                $table->json('erp_task_templates')->nullable()->after('erp_staff_ids');
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            foreach ([
                'erp_post_sale_enabled',
                'erp_category_id',
                'erp_category_name',
                'erp_subcategory_id',
                'erp_subcategory_name',
                'erp_staff_ids',
                'erp_task_templates',
            ] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
