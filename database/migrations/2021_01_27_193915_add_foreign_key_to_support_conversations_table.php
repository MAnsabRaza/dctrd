<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToSupportConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('support_conversations', function (Blueprint $table) {
                $table->foreign('support_id')->on('supports')->references('id')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key might already exist
        }
        
        try {
            Schema::table('support_conversations', function (Blueprint $table) {
                $table->foreign('sender_id')->on('users')->references('id')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key might already exist
        }
    }
}
