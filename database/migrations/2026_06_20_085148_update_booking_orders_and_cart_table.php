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
         * Pattern: exactly like product_orders table
         * NO foreign key constraints on booking_orders itself
         * Only plain integer/biginteger columns (same as product_orders)
         */

        // Step 1: Drop user_id FK if exists
        $fkName = $this->findForeignKeyNameForColumn('booking_orders', 'user_id');
        if (!empty($fkName)) {
            Schema::table('booking_orders', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });
        }

        // Step 2: Drop all old columns
        Schema::table('booking_orders', function (Blueprint $table) {
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

        // Step 3: Add new columns — NO foreign key constraints (same as product_orders)
        Schema::table('booking_orders', function (Blueprint $table) {

            // booking_id: same as product_id in product_orders (integer unsigned)
            if (!Schema::hasColumn('booking_orders', 'booking_id')) {
                $table->integer('booking_id')->unsigned()->nullable();
            }

            // seller_id: same as product_orders
            if (!Schema::hasColumn('booking_orders', 'seller_id')) {
                $table->integer('seller_id')->unsigned()->nullable();
            }

            // buyer_id: same as product_orders
            if (!Schema::hasColumn('booking_orders', 'buyer_id')) {
                $table->integer('buyer_id')->unsigned()->nullable();
            }

            // sale_id: same as product_orders
            if (!Schema::hasColumn('booking_orders', 'sale_id')) {
                $table->integer('sale_id')->unsigned()->nullable();
            }

            // booking_discount_id: same as discount_id in product_orders
            if (!Schema::hasColumn('booking_orders', 'booking_discount_id')) {
                $table->integer('booking_discount_id')->unsigned()->nullable();
            }

            // specifications: same as product_orders
            if (!Schema::hasColumn('booking_orders', 'specifications')) {
                $table->text('specifications')->nullable();
            }

            // quantity: same as product_orders
            if (!Schema::hasColumn('booking_orders', 'quantity')) {
                $table->integer('quantity')->nullable();
            }

            // message_to_seller: same as product_orders
            if (!Schema::hasColumn('booking_orders', 'message_to_seller')) {
                $table->text('message_to_seller')->nullable();
            }

            // tracking_code: same as product_orders
            if (!Schema::hasColumn('booking_orders', 'tracking_code')) {
                $table->string('tracking_code')->nullable();
            }

            // status: enum same as product_orders
            if (!Schema::hasColumn('booking_orders', 'status')) {
                $table->enum('status', [
                    'pending',
                    'confirmed',
                    'cancelled',
                    'completed'
                ])->default('pending');
            }

            // created_at: bigInteger unsigned — same as product_orders
            if (!Schema::hasColumn('booking_orders', 'created_at')) {
                $table->bigInteger('created_at')->unsigned()->nullable();
            }
        });

        /**
         * ==========================
         * CART UPDATE
         * ==========================
         * Pattern: exactly like product_orders migration's cart update
         * cart gets booking_order_id and booking_discount_id with FKs
         */

        // Drop old booking_id from cart if exists
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

        // Add new cart columns + FK (same as product_orders migration does for cart)
        Schema::table('cart', function (Blueprint $table) {

            if (!Schema::hasColumn('cart', 'booking_order_id')) {
                // integer unsigned — matches booking_orders.id (increments = INT UNSIGNED)
                $table->integer('booking_order_id')->unsigned()->nullable()->after('webinar_id');
            }

            if (!Schema::hasColumn('cart', 'booking_discount_id')) {
                $table->integer('booking_discount_id')->unsigned()->nullable()->after('booking_order_id');
            }

            // FK on cart — same pattern as product_orders migration
            if (!$this->foreignKeyExists('cart', 'cart_booking_order_id_foreign')) {
                $table->foreign('booking_order_id')
                    ->on('booking_orders')
                    ->references('id')
                    ->cascadeOnDelete();
            }

            if (Schema::hasTable('booking_discounts') && !$this->foreignKeyExists('cart', 'cart_booking_discount_id_foreign')) {
                $table->foreign('booking_discount_id')
                    ->on('booking_discounts')
                    ->references('id')
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
         * ==========================
         * BOOKING ORDERS ROLLBACK
         * ==========================
         */
        Schema::table('booking_orders', function (Blueprint $table) {
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

        // Restore old structure
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
                $table->enum('status', [
                    'pending', 'confirmed', 'cancelled', 'completed', 'no_show'
                ])->default('pending');
            }
            if (!Schema::hasColumn('booking_orders', 'payment_status')) {
                $table->enum('payment_status', [
                    'unpaid', 'partial', 'paid', 'refunded'
                ])->default('unpaid');
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
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }
    }
}