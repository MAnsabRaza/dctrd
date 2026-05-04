<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // ======================
        // 1. ADD MISSING COLUMNS
        // ======================

        Schema::table('bookings', function (Blueprint $table) {

            // Pricing
            if (!Schema::hasColumn('bookings', 'price_per')) {
                $table->decimal('price_per', 12, 2)->nullable()->after('price');
            }

            if (!Schema::hasColumn('bookings', 'price_unit')) {
                $table->string('price_unit')->nullable()->after('price_per');
            }

            if (!Schema::hasColumn('bookings', 'discount_price')) {
                $table->decimal('discount_price', 12, 2)->nullable()->after('price_unit');
            }

            if (!Schema::hasColumn('bookings', 'deposit_enabled')) {
                $table->boolean('deposit_enabled')->default(false);
            }

            if (!Schema::hasColumn('bookings', 'deposit_amount')) {
                $table->decimal('deposit_amount', 12, 2)->nullable();
            }

            if (!Schema::hasColumn('bookings', 'deposit_type')) {
                $table->string('deposit_type')->nullable();
            }

            // Capacity
            if (!Schema::hasColumn('bookings', 'capacity')) {
                $table->integer('capacity')->nullable();
            }

            if (!Schema::hasColumn('bookings', 'min_persons')) {
                $table->integer('min_persons')->default(1);
            }

            if (!Schema::hasColumn('bookings', 'max_persons')) {
                $table->integer('max_persons')->nullable();
            }

            if (!Schema::hasColumn('bookings', 'max_children')) {
                $table->integer('max_children')->nullable();
            }

            if (!Schema::hasColumn('bookings', 'children_allowed')) {
                $table->boolean('children_allowed')->default(true);
            }

            // Duration
            if (!Schema::hasColumn('bookings', 'duration_minutes')) {
                $table->integer('duration_minutes')->nullable();
            }

            if (!Schema::hasColumn('bookings', 'buffer_before')) {
                $table->integer('buffer_before')->default(0);
            }

            if (!Schema::hasColumn('bookings', 'buffer_after')) {
                $table->integer('buffer_after')->default(0);
            }

            if (!Schema::hasColumn('bookings', 'lead_time_hours')) {
                $table->integer('lead_time_hours')->default(0);
            }

            if (!Schema::hasColumn('bookings', 'cutoff_time_hours')) {
                $table->integer('cutoff_time_hours')->default(0);
            }

            // Booking options
            if (!Schema::hasColumn('bookings', 'instant_booking')) {
                $table->boolean('instant_booking')->default(true);
            }

            if (!Schema::hasColumn('bookings', 'requires_approval')) {
                $table->boolean('requires_approval')->default(false);
            }

            if (!Schema::hasColumn('bookings', 'allow_reschedule')) {
                $table->boolean('allow_reschedule')->default(true);
            }

            if (!Schema::hasColumn('bookings', 'reschedule_before_hours')) {
                $table->integer('reschedule_before_hours')->default(24);
            }

            if (!Schema::hasColumn('bookings', 'waitlist_enabled')) {
                $table->boolean('waitlist_enabled')->default(false);
            }

            if (!Schema::hasColumn('bookings', 'inventory')) {
                $table->integer('inventory')->nullable();
            }

            // Location
            if (!Schema::hasColumn('bookings', 'location_enabled')) {
                $table->boolean('location_enabled')->default(false);
            }

            if (!Schema::hasColumn('bookings', 'address_line')) {
                $table->string('address_line')->nullable();
            }

            if (!Schema::hasColumn('bookings', 'city')) {
                $table->string('city')->nullable();
            }

            if (!Schema::hasColumn('bookings', 'state')) {
                $table->string('state')->nullable();
            }

            if (!Schema::hasColumn('bookings', 'country')) {
                $table->string('country')->nullable();
            }

            if (!Schema::hasColumn('bookings', 'postal_code')) {
                $table->string('postal_code')->nullable();
            }

            if (!Schema::hasColumn('bookings', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable();
            }

            if (!Schema::hasColumn('bookings', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable();
            }

            // Extras
            if (!Schema::hasColumn('bookings', 'forum_enabled')) {
                $table->boolean('forum_enabled')->default(false);
            }

            if (!Schema::hasColumn('bookings', 'comments_enabled')) {
                $table->boolean('comments_enabled')->default(true);
            }

            if (!Schema::hasColumn('bookings', 'reviews_enabled')) {
                $table->boolean('reviews_enabled')->default(true);
            }

            if (!Schema::hasColumn('bookings', 'checkout_message')) {
                $table->text('checkout_message')->nullable();
            }

            if (!Schema::hasColumn('bookings', 'reviewer_message')) {
                $table->text('reviewer_message')->nullable();
            }

            if (!Schema::hasColumn('bookings', 'meta')) {
                $table->json('meta')->nullable();
            }

            // Status
            if (!Schema::hasColumn('bookings', 'status')) {
                $table->enum('status', ['draft', 'pending', 'published', 'rejected', 'inactive'])->default('draft');
            }

            if (!Schema::hasColumn('bookings', 'featured')) {
                $table->boolean('featured')->default(false);
            }

            if (!Schema::hasColumn('bookings', 'sales')) {
                $table->integer('sales')->default(0);
            }

            if (!Schema::hasColumn('bookings', 'views')) {
                $table->integer('views')->default(0);
            }

            if (!Schema::hasColumn('bookings', 'rating')) {
                $table->decimal('rating', 3, 1)->default(0);
            }

            if (!Schema::hasColumn('bookings', 'review_count')) {
                $table->integer('review_count')->default(0);
            }
        });

        // ======================
        // 2. FIX COLUMN TYPES (FK ISSUE)
        // ======================

        DB::statement('ALTER TABLE bookings MODIFY creator_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE bookings MODIFY category_id BIGINT UNSIGNED NULL');

        // ======================
        // 3. UNIQUE + INDEX + FK (SAFE)
        // ======================

        try {
            DB::statement('ALTER TABLE bookings ADD UNIQUE slug_unique (slug)');
        } catch (\Exception $e) {}

        try {
            DB::statement('CREATE INDEX idx_creator_id ON bookings (creator_id)');
        } catch (\Exception $e) {}

        try {
            DB::statement('CREATE INDEX idx_category_id ON bookings (category_id)');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE bookings 
                ADD CONSTRAINT fk_creator 
                FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE bookings 
                ADD CONSTRAINT fk_category 
                FOREIGN KEY (category_id) REFERENCES booking_categories(id) ON DELETE SET NULL');
        } catch (\Exception $e) {}
    }

    public function down()
    {
        try {
            DB::statement('ALTER TABLE bookings DROP FOREIGN KEY fk_creator');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE bookings DROP FOREIGN KEY fk_category');
        } catch (\Exception $e) {}

        try {
            DB::statement('DROP INDEX idx_creator_id ON bookings');
        } catch (\Exception $e) {}

        try {
            DB::statement('DROP INDEX idx_category_id ON bookings');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE bookings DROP INDEX slug_unique');
        } catch (\Exception $e) {}
    }
};