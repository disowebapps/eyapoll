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
        // Add indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['created_at'], 'users_created_at_idx');
            $table->index(['status', 'created_at'], 'users_status_created_at_idx');
        });

        // Add indexes for id_documents table
        Schema::table('id_documents', function (Blueprint $table) {
            $table->index(['created_at'], 'id_documents_created_at_idx');
            $table->index(['status', 'created_at'], 'id_documents_status_created_at_idx');
        });

        // Add indexes for candidate_documents table
        Schema::table('candidate_documents', function (Blueprint $table) {
            $table->index(['created_at'], 'candidate_documents_created_at_idx');
            $table->index(['status', 'created_at'], 'candidate_documents_status_created_at_idx');
            $table->index(['candidate_id', 'status'], 'candidate_documents_candidate_status_idx');
        });

        // Add indexes for candidates table
        Schema::table('candidates', function (Blueprint $table) {
            $table->index(['created_at'], 'candidates_created_at_idx');
            $table->index(['user_id', 'status'], 'candidates_user_status_idx');
            $table->index(['election_id', 'status'], 'candidates_election_status_idx');
        });

        // Add additional indexes for audit_logs table
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['entity_type', 'created_at'], 'audit_logs_entity_created_at_idx');
            $table->index(['created_at', 'action'], 'audit_logs_created_at_action_idx');
        });

        // Add indexes for notification_queues table
        Schema::table('notification_queues', function (Blueprint $table) {
            $table->index(['created_at'], 'notification_queues_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_created_at_idx');
            $table->dropIndex('users_status_created_at_idx');
        });

        // Drop indexes for id_documents table
        Schema::table('id_documents', function (Blueprint $table) {
            $table->dropIndex('id_documents_created_at_idx');
            $table->dropIndex('id_documents_status_created_at_idx');
        });

        // Drop indexes for candidate_documents table
        Schema::table('candidate_documents', function (Blueprint $table) {
            $table->dropIndex('candidate_documents_created_at_idx');
            $table->dropIndex('candidate_documents_status_created_at_idx');
            $table->dropIndex('candidate_documents_candidate_status_idx');
        });

        // Drop indexes for candidates table
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropIndex('candidates_created_at_idx');
            $table->dropIndex('candidates_user_status_idx');
            $table->dropIndex('candidates_election_status_idx');
        });

        // Drop additional indexes for audit_logs table
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_entity_created_at_idx');
            $table->dropIndex('audit_logs_created_at_action_idx');
        });

        // Drop indexes for notification_queues table
        Schema::table('notification_queues', function (Blueprint $table) {
            $table->dropIndex('notification_queues_created_at_idx');
        });
    }
};
