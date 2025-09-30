<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add fields to id_documents table
        Schema::table('id_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_reviewer_id')->nullable()->after('reviewed_by');
            $table->timestamp('assigned_at')->nullable()->after('assigned_reviewer_id');
            $table->timestamp('escalated_at')->nullable()->after('assigned_at');
            $table->timestamp('review_started_at')->nullable()->after('escalated_at');
            $table->timestamp('review_completed_at')->nullable()->after('review_started_at');

            $table->foreign('assigned_reviewer_id')->references('id')->on('admins')->onDelete('set null');

            // Indexes for performance
            $table->index(['assigned_reviewer_id', 'status']);
            $table->index(['assigned_at']);
            $table->index(['escalated_at']);
            $table->index(['review_started_at', 'review_completed_at']);
        });

        // Add fields to candidate_documents table
        Schema::table('candidate_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_reviewer_id')->nullable()->after('reviewed_by');
            $table->timestamp('assigned_at')->nullable()->after('assigned_reviewer_id');
            $table->timestamp('escalated_at')->nullable()->after('assigned_at');
            $table->timestamp('review_started_at')->nullable()->after('escalated_at');
            $table->timestamp('review_completed_at')->nullable()->after('review_started_at');

            $table->foreign('assigned_reviewer_id')->references('id')->on('admins')->onDelete('set null');

            // Indexes for performance
            $table->index(['assigned_reviewer_id', 'status']);
            $table->index(['assigned_at']);
            $table->index(['escalated_at']);
            $table->index(['review_started_at', 'review_completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fields from id_documents table
        Schema::table('id_documents', function (Blueprint $table) {
            $table->dropIndex(['assigned_reviewer_id', 'status']);
            $table->dropIndex(['assigned_at']);
            $table->dropIndex(['escalated_at']);
            $table->dropIndex(['review_started_at', 'review_completed_at']);
            $table->dropForeign(['assigned_reviewer_id']);
            $table->dropColumn(['assigned_reviewer_id', 'assigned_at', 'escalated_at', 'review_started_at', 'review_completed_at']);
        });

        // Remove fields from candidate_documents table
        Schema::table('candidate_documents', function (Blueprint $table) {
            $table->dropIndex(['assigned_reviewer_id', 'status']);
            $table->dropIndex(['assigned_at']);
            $table->dropIndex(['escalated_at']);
            $table->dropIndex(['review_started_at', 'review_completed_at']);
            $table->dropForeign(['assigned_reviewer_id']);
            $table->dropColumn(['assigned_reviewer_id', 'assigned_at', 'escalated_at', 'review_started_at', 'review_completed_at']);
        });
    }
};