<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateBookingOrdersAndCartTable extends Migration
{
    /**
     * Check if a foreign key constraint exists on a given table.
     */
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

    /**
     * Find the actual foreign key constraint name for a given column.
     */
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

    /**
     * Get the column type of a given table's column from information_schema.
     * Returns e.g. 'bigint unsigned', 'int unsigned', etc.
     */
    private function getColumnType(string $table, string $column): ?string
    {
        $database = DB::connection()->getDatabaseName();

        $result = DB::select("
            SELECT DATA_TYPE, COLUMN_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ", [$database, $table, $column]);

        if (empty($result)) {
            return null;
        }

        return strtolower($result[0]->COLUMN_TYPE);
    }

    public function up()
    {
        /**
         * ==========================
         * BOOKING ORDERS UPDATE
         * ==========================
         */

        // Drop the user_id foreign key if it exists
        $fkName = $this->findForeignKeyNameForColumn('booking_orders', 'user_id');
        if (!empty($fkName)) {
            Schema::table('booking_orders', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });
        }

        // Drop old columns
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

        // Add new columns
        Schema::table('booking_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_orders', 'booking_id')) {
                // bookings.id is bigIncrements (BIGINT UNSIGNED) — match it
                $table->unsignedBigInteger('booking_id')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'seller_id')) {
                // users.id is INT UNSIGNED — match it
                $table->unsignedInteger('seller_id')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'buyer_id')) {
                $table->unsignedInteger('buyer_id')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'sale_id')) {
                $table->unsignedBigInteger('sale_id')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'booking_discount_id')) {
                $table->unsignedBigInteger('booking_discount_id')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'specifications')) {
                $table->text('specifications')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'quantity')) {
                $table->integer('quantity')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'message_to_seller')) {
                $table->text('message_to_seller')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'tracking_code')) {
                $table->string('tracking_code')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'status')) {
                $table->enum('status', [
                    'pending',
                    'confirmed',
                    'cancelled',
                    'completed'
                ])->default('pending');
            }
            if (!Schema::hasColumn('booking_orders', 'created_at')) {
                $table->unsignedBigInteger('created_at')->nullable();
            }
        });

        // Add foreign keys
        Schema::table('booking_orders', function (Blueprint $table) {

            // bookings.id = BIGINT UNSIGNED, booking_id = BIGINT UNSIGNED ✅
            if (Schema::hasTable('bookings') && !$this->foreignKeyExists('booking_orders', 'booking_orders_booking_id_foreign')) {
                $table->foreign('booking_id')
                    ->references('id')
                    ->on('bookings')
                    ->nullOnDelete();
            }

            // users.id = INT UNSIGNED, seller_id = INT UNSIGNED ✅
            if (!$this->foreignKeyExists('booking_orders', 'booking_orders_seller_id_foreign')) {
                $table->foreign('seller_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }

            if (!$this->foreignKeyExists('booking_orders', 'booking_orders_buyer_id_foreign')) {
                $table->foreign('buyer_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }

            // Check sales table exists before adding FK
            if (Schema::hasTable('sales') && !$this->foreignKeyExists('booking_orders', 'booking_orders_sale_id_foreign')) {
                $table->foreign('sale_id')
                    ->references('id')
                    ->on('sales')
                    ->nullOnDelete();
            }

            if (Schema::hasTable('booking_discounts') && !$this->foreignKeyExists('booking_orders', 'booking_orders_booking_discount_id_foreign')) {
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

        Schema::table('cart', function (Blueprint $table) {
            if (!Schema::hasColumn('cart', 'booking_order_id')) {
                // booking_orders.id = bigIncrements (BIGINT UNSIGNED)
                $table->unsignedBigInteger('booking_order_id')->nullable()->after('webinar_id');
            }
            if (!Schema::hasColumn('cart', 'booking_discount_id')) {
                $table->unsignedBigInteger('booking_discount_id')->nullable()->after('booking_order_id');
            }
        });

        Schema::table('cart', function (Blueprint $table) {
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
         * ==========================
         * CART ROLLBACK
         * ==========================
         */
        $cartOrderFk    = $this->findForeignKeyNameForColumn('cart', 'booking_order_id');
        $cartDiscountFk = $this->findForeignKeyNameForColumn('cart', 'booking_discount_id');

        if (!empty($cartOrderFk) || !empty($cartDiscountFk)) {
            Schema::table('cart', function (Blueprint $table) use ($cartOrderFk, $cartDiscountFk) {
                if (!empty($cartOrderFk)) {
                    $table->dropForeign($cartOrderFk);
                }
                if (!empty($cartDiscountFk)) {
                    $table->dropForeign($cartDiscountFk);
                }
            });
        }

        Schema::table('cart', function (Blueprint $table) {
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
        $columnsWithFk = ['booking_id', 'seller_id', 'buyer_id', 'sale_id', 'booking_discount_id'];
        $fksToRemove   = [];

        foreach ($columnsWithFk as $col) {
            $fkName = $this->findForeignKeyNameForColumn('booking_orders', $col);
            if (!empty($fkName)) {
                $fksToRemove[] = $fkName;
            }
        }

        if (!empty($fksToRemove)) {
            Schema::table('booking_orders', function (Blueprint $table) use ($fksToRemove) {
                foreach ($fksToRemove as $fkName) {
                    $table->dropForeign($fkName);
                }
            });
        }

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