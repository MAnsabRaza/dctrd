<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBookingOrdersAndCartTable extends Migration
{
    public function up()
    {
        /**
         * 🔴 STEP 1: booking_orders table update
         */
        Schema::table('booking_orders', function (Blueprint $table) {

            // ⚠️ foreign key drop (agar exist karti ho)
            $table->dropForeign(['user_id']);

            // 🔥 old columns drop
            $table->dropColumn([
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
                'created_at',
                'updated_at',
            ]);
        });

        Schema::table('booking_orders', function (Blueprint $table) {

            // ✅ new structure (product_orders style)
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->unsignedBigInteger('booking_discount_id')->nullable();

            $table->text('specifications')->nullable();
            $table->integer('quantity')->unsigned();

            $table->text('message_to_seller')->nullable();
            $table->string('tracking_code')->nullable();

            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed']);

            $table->bigInteger('created_at')->unsigned();

            // ✅ foreign keys
            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->foreign('seller_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('buyer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('sale_id')->references('id')->on('sales')->nullOnDelete();
            $table->foreign('booking_discount_id')->references('id')->on('booking_discounts')->nullOnDelete();
        });


        /**
         * 🔴 STEP 2: cart table update
         */
        Schema::table('cart', function (Blueprint $table) {

            // old remove (agar exist ho)
            if (Schema::hasColumn('cart', 'booking_id')) {
                $table->dropForeign(['booking_id']);
                $table->dropColumn('booking_id');
            }

            // new add
            $table->unsignedBigInteger('booking_order_id')->nullable()->after('webinar_id');
            $table->unsignedBigInteger('booking_discount_id')->nullable()->after('booking_order_id');

            $table->foreign('booking_order_id')->references('id')->on('booking_orders')->cascadeOnDelete();
            $table->foreign('booking_discount_id')->references('id')->on('booking_discounts')->nullOnDelete();
        });
    }

    public function down()
    {
        /**
         * 🔴 rollback cart
         */
        Schema::table('cart', function (Blueprint $table) {
            $table->dropForeign(['booking_order_id']);
            $table->dropForeign(['booking_discount_id']);

            $table->dropColumn(['booking_order_id', 'booking_discount_id']);
        });

        /**
         * 🔴 rollback booking_orders
         */
        Schema::table('booking_orders', function (Blueprint $table) {

            $table->dropForeign(['booking_id']);
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['buyer_id']);
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['booking_discount_id']);

            $table->dropColumn([
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
            ]);
        });

        // old columns back (optional)
        Schema::table('booking_orders', function (Blueprint $table) {

            $table->string('order_number')->unique();
            $table->unsignedInteger('user_id')->index();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
}