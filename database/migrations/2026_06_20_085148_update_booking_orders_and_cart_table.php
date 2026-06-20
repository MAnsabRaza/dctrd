<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateBookingOrdersAndCartTable extends Migration
{
    private function foreignKeyExists(string $table, string $foreignKeyName): bool
    {
        $database = DB::connection()->getDatabaseName();
        $result = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = ?
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$database, $table, $foreignKeyName]);
        return count($result) > 0;
    }

    private function findForeignKeyNameForColumn(string $table, string $column): ?string
    {
        $database = DB::connection()->getDatabaseName();
        $result = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$database, $table, $column]);
        return $result[0]->CONSTRAINT_NAME ?? null;
    }

    public function up()
    {
        /**
         * ==========================
         * BOOKING ORDERS UPDATE
         * ==========================
         * booking_orders.id = bigint(20) unsigned  ✅ confirmed from DESCRIBE
         * All reference columns = bigint unsigned to match
         */

        // Step 1: Drop user_id FK if exists
        $fkName = $this->findForeignKeyNameForColumn('booking_orders', 'user_id');
        if (!empty($fkName)) {
            Schema::table('booking_orders', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });
        }

        // Step 2: Drop old columns
        Schema::table('booking_orders', function (Blueprint $table) {
            $dropColumns = [
                'order_number', 'user_id', 'subtotal', 'discount_amount',
                'tax_amount', 'total', 'currency', 'status',
                'payment_status', 'notes', 'updated_at',
            ];
            foreach ($dropColumns as $col) {
                if (Schema::hasColumn('booking_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Step 3: Add new columns — NO FK on booking_orders itself
        Schema::table('booking_orders', function (Blueprint $table) {

            // booking_id — bookings.id = bigint unsigned ✅
            if (!Schema::hasColumn('booking_orders', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')->nullable();
            }
            // seller_id — users.id = int unsigned (but db shows bigint, keep bigint)
            if (!Schema::hasColumn('booking_orders', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->nullable();
            }
            // buyer_id
            if (!Schema::hasColumn('booking_orders', 'buyer_id')) {
                $table->unsignedBigInteger('buyer_id')->nullable();
            }
            // sale_id
            if (!Schema::hasColumn('booking_orders', 'sale_id')) {
                $table->unsignedBigInteger('sale_id')->nullable();
            }
            // booking_discount_id
            if (!Schema::hasColumn('booking_orders', 'booking_discount_id')) {
                $table->unsignedBigInteger('booking_discount_id')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'specifications')) {
                $table->text('specifications')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'quantity')) {
                $table->integer('quantity')->unsigned()->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'message_to_seller')) {
                $table->text('message_to_seller')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'tracking_code')) {
                $table->string('tracking_code')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'status')) {
                $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            }
            if (!Schema::hasColumn('booking_orders', 'created_at')) {
                $table->unsignedBigInteger('created_at')->nullable();
            }
        });

        /**
         * ==========================
         * CART UPDATE
         * ==========================
         * booking_orders.id = bigint(20) unsigned
         * cart.booking_order_id   = unsignedBigInteger  ✅ matches
         * cart.booking_discount_id = unsignedBigInteger ✅ matches booking_discounts.id
         */

        // Drop old cart booking_id column if exists
        if (Schema::hasColumn('cart', 'booking_id')) {
            $cartFkName = $this->findForeignKeyNameForColumn('cart', 'booking_id');
            if (!empty($cartFkName)) {
                Schema::table('cart', function (Blueprint $table) use ($cartFkName) {
                    $table->dropForeign($cartFkName);
                });
            }
            Schema::table('cart', function (Blueprint $table) {
                $table->dropColumn('booking_id');
            });
        }

        // Add booking_order_id and booking_discount_id to existing cart table
        Schema::table('cart', function (Blueprint $table) {

            if (!Schema::hasColumn('cart', 'booking_order_id')) {
                // BIGINT UNSIGNED — matches booking_orders.id (bigint unsigned) ✅
                $table->unsignedBigInteger('booking_order_id')->nullable()->after('webinar_id');
            }

            if (!Schema::hasColumn('cart', 'booking_discount_id')) {
                $table->unsignedBigInteger('booking_discount_id')->nullable()->after('booking_order_id');
            }

            if (!$this->foreignKeyExists('cart', 'cart_booking_order_id_foreign')) {
                $table->foreign('booking_order_id')
                    ->references('id')
                    ->on('booking_orders')
                    ->cascadeOnDelete();
            }

            if (Schema::hasTable('booking_discounts') && !$this->foreignKeyExists('cart', 'cart_booking_discount_id_foreign')) {
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
         * CART ROLLBACK
         */
        $cartOrderFk    = $this->findForeignKeyNameForColumn('cart', 'booking_order_id');
        $cartDiscountFk = $this->findForeignKeyNameForColumn('cart', 'booking_discount_id');

        Schema::table('cart', function (Blueprint $table) use ($cartOrderFk, $cartDiscountFk) {
            if (!empty($cartOrderFk)) {
                $table->dropForeign($cartOrderFk);
            }
            if (!empty($cartDiscountFk)) {
                $table->dropForeign($cartDiscountFk);
            }
            if (Schema::hasColumn('cart', 'booking_order_id')) {
                $table->dropColumn('booking_order_id');
            }
            if (Schema::hasColumn('cart', 'booking_discount_id')) {
                $table->dropColumn('booking_discount_id');
            }
        });

        /**
         * BOOKING ORDERS ROLLBACK
         */
        Schema::table('booking_orders', function (Blueprint $table) {
            $dropNew = [
                'booking_id', 'seller_id', 'buyer_id', 'sale_id',
                'booking_discount_id', 'specifications', 'quantity',
                'message_to_seller', 'tracking_code', 'status', 'created_at',
            ];
            foreach ($dropNew as $col) {
                if (Schema::hasColumn('booking_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('booking_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_orders', 'order_number')) {
                $table->string('order_number')->unique();
            }
            if (!Schema::hasColumn('booking_orders', 'user_id')) {
                $table->unsignedInteger('user_id')->index();
            }
            if (!Schema::hasColumn('booking_orders', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('booking_orders', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('booking_orders', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('booking_orders', 'total')) {
                $table->decimal('total', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('booking_orders', 'currency')) {
                $table->string('currency', 10)->default('USD');
            }
            if (!Schema::hasColumn('booking_orders', 'status')) {
                $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            }
            if (!Schema::hasColumn('booking_orders', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            }
            if (!Schema::hasColumn('booking_orders', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (!$this->foreignKeyExists('booking_orders', 'booking_orders_user_id_foreign')) {
            Schema::table('booking_orders', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }
}