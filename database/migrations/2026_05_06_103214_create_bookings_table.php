<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {

            $table->id();

            // ✅ MATCH users.id (INT UNSIGNED)
            $table->unsignedInteger('creator_id');
            $table->unsignedBigInteger('category_id')->nullable(); // category BIGINT hai

            // Basic info
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('booking_type');
            $table->string('sub_type')->nullable();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('language', 10)->default('en');
            $table->string('thumbnail')->nullable();
            $table->string('cover')->nullable();
               $table->integer('order')->default(0);

            // Pricing
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('price_per', 12, 2)->nullable();
            $table->string('price_unit')->nullable();
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->decimal('tax', 5, 2)->default(0);
            $table->decimal('commission', 5, 2)->default(0);
            $table->boolean('deposit_enabled')->default(false);
            $table->decimal('deposit_amount', 12, 2)->nullable();
            $table->string('deposit_type')->nullable();

            // Capacity
            $table->integer('capacity')->nullable();
            $table->integer('min_persons')->default(1);
            $table->integer('max_persons')->nullable();
            $table->integer('max_children')->nullable();
            $table->boolean('children_allowed')->default(true);

            // Duration
            $table->integer('duration_minutes')->nullable();
            $table->integer('buffer_before')->default(0);
            $table->integer('buffer_after')->default(0);
            $table->integer('lead_time_hours')->default(0);
            $table->integer('cutoff_time_hours')->default(0);

            // Booking options
            $table->boolean('instant_booking')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('allow_reschedule')->default(true);
            $table->integer('reschedule_before_hours')->default(24);
            $table->boolean('waitlist_enabled')->default(false);
            $table->integer('inventory')->nullable();

            // Location
            $table->boolean('location_enabled')->default(false);
            $table->string('address_line')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // Extras
            $table->boolean('forum_enabled')->default(false);
            $table->boolean('comments_enabled')->default(true);
            $table->boolean('reviews_enabled')->default(true);
            $table->text('checkout_message')->nullable();
            $table->text('reviewer_message')->nullable();
            $table->json('meta')->nullable();

            // Status
            $table->enum('status', ['draft', 'pending', 'published', 'rejected', 'inactive'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->integer('sales')->default(0);
            $table->integer('views')->default(0);
            $table->decimal('rating', 3, 1)->default(0);
            $table->integer('review_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // ✅ Foreign Keys (manual, correct types)
            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('category_id')
                ->references('id')
                ->on('booking_categories')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};