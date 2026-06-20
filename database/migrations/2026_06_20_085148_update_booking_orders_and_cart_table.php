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
         */

        // Drop user_id FK if exists
        $fkName = $this->findForeignKeyNameForColumn('booking_orders', 'user_id');
        if (!empty($fkName)) {
            Schema::table('booking_orders', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });
        }

        // Drop old columns
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

        // Add new columns — all bigint to match booking_orders.id (bigint unsigned)
        Schema::table('booking_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_orders', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->nullable();
            }
            if (!Schema::hasColumn('booking_orders', 'buyer_id')) {
                $table->unsignedBigInteger('buyer_id')->nullable();
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
         *
         * IMPORTANT:
         * cart.booking_order_id already exists as int(10) unsigned (wrong type)
         * booking_orders.id = bigint(20) unsigned
         * So we must MODIFY existing columns to bigint, not ADD them
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

        // Drop existing FK on booking_order_id if somehow exists (cleanup)
        $existingFk = $this->findForeignKeyNameForColumn('cart', 'booking_order_id');
        if (!empty($existingFk)) {
            Schema::table('cart', function (Blueprint $table) use ($existingFk) {
                $table->dropForeign($existingFk);
            });
        }
        $existingDiscountFk = $this->findForeignKeyNameForColumn('cart', 'booking_discount_id');
        if (!empty($existingDiscountFk)) {
            Schema::table('cart', function (Blueprint $table) use ($existingDiscountFk) {
                $table->dropForeign($existingDiscountFk);
            });
        }

        // MODIFY columns to correct bigint type (whether they exist or not)
        // Using raw SQL to safely handle both cases: column exists (MODIFY) or not (ADD)
        $database = DB::connection()->getDatabaseName();

        $bookingOrderColExists = Schema::hasColumn('cart', 'booking_order_id');
        $bookingDiscountColExists = Schema::hasColumn('cart', 'booking_discount_id');

        if ($bookingOrderColExists) {
            // Column exists with wrong INT type — MODIFY to BIGINT
            DB::statement('ALTER TABLE `cart` MODIFY COLUMN `booking_order_id` BIGINT UNSIGNED NULL');
        } else {
            // Column doesn't exist — ADD with correct type
            DB::statement('ALTER TABLE `cart` ADD COLUMN `booking_order_id` BIGINT UNSIGNED NULL AFTER `webinar_id`');
        }

        if ($bookingDiscountColExists) {
            // Column exists with wrong INT type — MODIFY to BIGINT
            DB::statement('ALTER TABLE `cart` MODIFY COLUMN `booking_discount_id` BIGINT UNSIGNED NULL');
        } else {
            // Column doesn't exist — ADD with correct type
            DB::statement('ALTER TABLE `cart` ADD COLUMN `booking_discount_id` BIGINT UNSIGNED NULL AFTER `booking_order_id`');
        }

        // Now add FK constraints — types match (both BIGINT UNSIGNED) ✅
        if (!$this->foreignKeyExists('cart', 'cart_booking_order_id_foreign')) {
            DB::statement('ALTER TABLE `cart` ADD CONSTRAINT `cart_booking_order_id_foreign` FOREIGN KEY (`booking_order_id`) REFERENCES `booking_orders`(`id`) ON DELETE CASCADE');
        }

        if (Schema::hasTable('booking_discounts') && !$this->foreignKeyExists('cart', 'cart_booking_discount_id_foreign')) {
            DB::statement('ALTER TABLE `cart` ADD CONSTRAINT `cart_booking_discount_id_foreign` FOREIGN KEY (`booking_discount_id`) REFERENCES `booking_discounts`(`id`) ON DELETE SET NULL');
        }
    }

    public function down()
    {
        // Drop cart FKs
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

        // Drop booking_orders new columns
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

        // Restore old booking_orders structure
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