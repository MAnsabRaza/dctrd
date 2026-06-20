<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBookingOrdersAndCartTable extends Migration
{
    public function up()
    {
        /**
         * ==========================
         * BOOKING ORDERS UPDATE
         * ==========================
         */
        Schema::table('booking_orders', function (Blueprint $table) {

            try { $table->dropForeign(['user_id']); } catch (\Exception $e) {}

            $dropColumns = [
                'order_number',
                'user_id',
                'subtotal',
                'discount_amount',
                'tax_amount',
                'total',
                'currency',
                'status',
                'payment_status',
                'notes',
                'updated_at',
            ];

            foreach ($dropColumns as $col) {
                if (Schema::hasColumn('booking_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('booking_orders', function (Blueprint $table) {

            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->unsignedBigInteger('booking_discount_id')->nullable();

            $table->text('specifications')->nullable();
            $table->integer('quantity')->nullable();

            $table->text('message_to_seller')->nullable();
            $table->string('tracking_code')->nullable();

            $table->enum('status', [
                'pending',
                'confirmed',
                'cancelled',
                'completed'
            ])->default('pending');

            $table->unsignedBigInteger('created_at')->nullable();
        });

        Schema::table('booking_orders', function (Blueprint $table) {

            if (Schema::hasTable('bookings')) {
                $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
            }

            $table->foreign('seller_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('buyer_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('sale_id')->references('id')->on('sales')->nullOnDelete();

            if (Schema::hasTable('booking_discounts')) {
                $table->foreign('booking_discount_id')
                    ->references('id')
                    ->on('booking_discounts')
                    ->nullOnDelete();
            }
        });

        /**
         * ==========================
         * CART UPDATE
         * ==========================
         */
        Schema::table('cart', function (Blueprint $table) {

            if (Schema::hasColumn('cart', 'booking_id')) {
                try { $table->dropForeign(['booking_id']); } catch (\Exception $e) {}
                $table->dropColumn('booking_id');
            }

            $table->unsignedBigInteger('booking_order_id')->nullable()->after('webinar_id');
            $table->unsignedBigInteger('booking_discount_id')->nullable()->after('booking_order_id');
        });

        Schema::table('cart', function (Blueprint $table) {

            $table->foreign('booking_order_id')
                ->references('id')
                ->on('booking_orders')
                ->cascadeOnDelete();

            if (Schema::hasTable('booking_discounts')) {
                $table->foreign('booking_discount_id')
                    ->references('id')
                    ->on('booking_discounts')
                    ->nullOnDelete();
            }
        });
    }

    public function down()
    {
        /**
         * ==========================
         * CART ROLLBACK
         * ==========================
         */
        Schema::table('cart', function (Blueprint $table) {

            try { $table->dropForeign(['booking_order_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['booking_discount_id']); } catch (\Exception $e) {}

            if (Schema::hasColumn('cart', 'booking_order_id')) {
                $table->dropColumn('booking_order_id');
            }

            if (Schema::hasColumn('cart', 'booking_discount_id')) {
                $table->dropColumn('booking_discount_id');
            }
        });

        /**
         * ==========================
         * BOOKING ORDERS ROLLBACK
         * ==========================
         */
        Schema::table('booking_orders', function (Blueprint $table) {

            try { $table->dropForeign(['booking_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['seller_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['buyer_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['sale_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['booking_discount_id']); } catch (\Exception $e) {}

            $dropNew = [
                'booking_id',
                'seller_id',
                'buyer_id',
                'sale_id',
                'booking_discount_id',
                'specifications',
                'quantity',
                'message_to_seller',
                'tracking_code',
                'status',
                'created_at',
            ];

            foreach ($dropNew as $col) {
                if (Schema::hasColumn('booking_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        /**
         * OLD STRUCTURE RESTORE
         */
        Schema::table('booking_orders', function (Blueprint $table) {

            $table->string('order_number')->unique();
            $table->unsignedInteger('user_id')->index();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->enum('status', [
                'pending',
                'confirmed',
                'cancelled',
                'completed',
                'no_show'
            ])->default('pending');
            $table->enum('payment_status', [
                'unpaid',
                'partial',
                'paid',
                'refunded'
            ])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
}