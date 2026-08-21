<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regulatory_form_submissions', function (Blueprint $table) {
            // form_submissions.id `increments()` (INT UNSIGNED) hai, isliye yahan bhi unsignedInteger
            $table->unsignedInteger('form_submission_id')->nullable()->after('form_id');
            $table->index('form_submission_id');

            $table->foreign('form_submission_id')
                ->references('id')->on('form_submissions')
                ->onDelete('set null');

            // review workflow ke liye
            $table->unsignedInteger('reviewed_by')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        // 'data' column ab mandatory nahi — answers ab form_submissions me hain,
        // ye column sirf purane (legacy) records ke liye backward-compatible rakha hai
        DB::statement('ALTER TABLE regulatory_form_submissions MODIFY data JSON NULL');
    }

    public function down(): void
    {
        Schema::table('regulatory_form_submissions', function (Blueprint $table) {
            $table->dropForeign(['form_submission_id']);
            $table->dropIndex(['form_submission_id']);
            $table->dropColumn(['form_submission_id', 'reviewed_by', 'reviewed_at']);
        });

        DB::statement('ALTER TABLE regulatory_form_submissions MODIFY data JSON NOT NULL');
    }
};