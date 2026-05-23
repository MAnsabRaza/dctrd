<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        $this->addSoftDeletes([
            'bookings',
            'booking_categories',
            'booking_resources',
            'booking_rate_plans',
            'booking_seasons',
            'booking_availabilities',
            'booking_policies',
            'booking_variants',
            'booking_specifications',
            'booking_bundles',
            'booking_orders',
            'booking_reviews',
            'booking_packages',
            'booking_comments',
        ]);

        if (!Schema::hasColumn('booking_imports', 'duplicate_rows')) {
            Schema::table('booking_imports', function (Blueprint $table) {
                $table->integer('duplicate_rows')->default(0)->after('failed_rows');
                $table->json('summary')->nullable()->after('errors');
                $table->timestamp('started_at')->nullable()->after('status');
                $table->timestamp('finished_at')->nullable()->after('started_at');
                $table->index(['type', 'status']);
            });
        }

        if (!Schema::hasColumn('exchange_rates', 'source_payload')) {
            Schema::table('exchange_rates', function (Blueprint $table) {
                $table->json('source_payload')->nullable()->after('provider');
                $table->unique(['base_currency', 'target_currency', 'provider', 'fetched_at'], 'exchange_rates_unique_snapshot');
            });
        }

        $this->createBookingFilters();
        $this->createBookingRules();
        $this->createBookingSlots();
        $this->createBookingDiscounts();
        $this->createBookingCoupons();
        $this->createBookingAssets();
        $this->createBookingReports();
        $this->createBookingFeatured();
        $this->createBookingWaitlists();
        $this->createBookingCalendarIntegrations();
        $this->createBookingImportLogs();
    }

    public function down()
    {
        foreach ([
            'booking_import_logs',
            'booking_calendar_integrations',
            'booking_waitlists',
            'booking_featured',
            'booking_reports',
            'booking_assets',
            'booking_coupons',
            'booking_discounts',
            'booking_slots',
            'booking_rules',
            'booking_filters',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function addSoftDeletes(array $tables): void
    {
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    private function createBookingFilters(): void
    {
        if (Schema::hasTable('booking_filters')) {
            return;
        }

        Schema::create('booking_filters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('title');
            $table->string('type')->default('checkbox');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('status')->default(true)->index();
            $table->integer('order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('booking_categories')->nullOnDelete();
        });
    }

    private function createBookingRules(): void
    {
        if (Schema::hasTable('booking_rules')) {
            return;
        }

        Schema::create('booking_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->string('rule_type')->index();
            $table->json('conditions')->nullable();
            $table->json('actions')->nullable();
            $table->date('starts_at')->nullable()->index();
            $table->date('ends_at')->nullable()->index();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
        });
    }

    private function createBookingSlots(): void
    {
        if (Schema::hasTable('booking_slots')) {
            return;
        }

        Schema::create('booking_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->unsignedBigInteger('resource_id')->nullable()->index();
            $table->tinyInteger('day_of_week')->nullable()->index();
            $table->date('date')->nullable()->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('capacity')->default(1);
            $table->integer('buffer_before')->default(0);
            $table->integer('buffer_after')->default(0);
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_id', 'date', 'day_of_week']);
            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->foreign('resource_id')->references('id')->on('booking_resources')->nullOnDelete();
        });
    }

    private function createBookingDiscounts(): void
    {
        if (Schema::hasTable('booking_discounts')) {
            return;
        }

        Schema::create('booking_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->unsignedBigInteger('bundle_id')->nullable()->index();
            $table->string('title');
            $table->string('discount_type')->default('percent');
            $table->decimal('amount', 12, 2);
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('expires_at')->nullable()->index();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->boolean('status')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->foreign('bundle_id')->references('id')->on('booking_bundles')->cascadeOnDelete();
        });
    }

    private function createBookingCoupons(): void
    {
        if (Schema::hasTable('booking_coupons')) {
            return;
        }

        Schema::create('booking_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->unsignedBigInteger('bundle_id')->nullable()->index();
            $table->string('discount_type')->default('percent');
            $table->decimal('amount', 12, 2);
            $table->decimal('minimum_order_amount', 12, 2)->default(0);
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('expires_at')->nullable()->index();
            $table->boolean('status')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->foreign('bundle_id')->references('id')->on('booking_bundles')->cascadeOnDelete();
        });
    }

    private function createBookingAssets(): void
    {
        if (Schema::hasTable('booking_assets')) {
            return;
        }

        Schema::create('booking_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->string('type')->default('image')->index();
            $table->string('path');
            $table->string('title')->nullable();
            $table->string('alt')->nullable();
            $table->integer('order')->default(0)->index();
            $table->boolean('status')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
        });
    }

    private function createBookingReports(): void
    {
        if (Schema::hasTable('booking_reports')) {
            return;
        }

        Schema::create('booking_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('reason')->index();
            $table->text('message')->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('reviewed_by')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('booking_orders')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    private function createBookingFeatured(): void
    {
        if (Schema::hasTable('booking_featured')) {
            return;
        }

        Schema::create('booking_featured', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('placement')->default('home')->index();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('expires_at')->nullable()->index();
            $table->integer('order')->default(0)->index();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('booking_categories')->cascadeOnDelete();
        });
    }

    private function createBookingWaitlists(): void
    {
        if (Schema::hasTable('booking_waitlists')) {
            return;
        }

        Schema::create('booking_waitlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();
            $table->unsignedBigInteger('resource_id')->nullable()->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->date('booking_date')->nullable()->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('persons')->default(1);
            $table->string('status')->default('waiting')->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['booking_id', 'resource_id', 'user_id', 'booking_date', 'start_time'], 'booking_waitlists_user_unique');
            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->foreign('resource_id')->references('id')->on('booking_resources')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    private function createBookingCalendarIntegrations(): void
    {
        if (Schema::hasTable('booking_calendar_integrations')) {
            return;
        }

        Schema::create('booking_calendar_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->string('provider')->index();
            $table->string('external_calendar_id')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
        });
    }

    private function createBookingImportLogs(): void
    {
        if (Schema::hasTable('booking_import_logs')) {
            return;
        }

        Schema::create('booking_import_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('import_id')->index();
            $table->integer('row_number')->nullable()->index();
            $table->string('level')->default('info')->index();
            $table->string('action')->nullable()->index();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->foreign('import_id')->references('id')->on('booking_imports')->cascadeOnDelete();
        });
    }
};
