<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkout_module_audits', function (Blueprint $table) {
            $table->id();
                        $table->unsignedInteger('order_id');
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');
 
            // Which module data was changed
            // Examples: 'days', 'hours', 'staff_member'
            $table->string('module_name', 100);
 
            // Previous value (JSON)
            $table->text('old_value')->nullable();
 
            // New value (JSON)
            $table->text('new_value')->nullable();
 
            // Who made this change (admin/org/customer user id)
            $table->unsignedInteger('changed_by');
            $table->foreign('changed_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
 
            // Why was this changed?
            // Examples: 'Customer reschedule request',
            //           'Admin correction', 'Cancellation'
            $table->text('reason')->nullable();
 
            // Index for fast order lookups
            $table->index('order_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checkout_module_audits');
    }
};
