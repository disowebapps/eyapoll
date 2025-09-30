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
        // Only add indexes that are specifically needed for voter dashboard performance
        Schema::table('vote_records', function (Blueprint $table) {
            // Index for voter dashboard queries (frequently queried by voter_hash)
            $table->index('voter_hash', 'vote_records_voter_hash_idx');

            // Index for cast_at queries in dashboard
            $table->index(['voter_hash', 'cast_at'], 'vote_records_voter_hash_cast_at_idx');
        });

        // Additional indexes for vote tokens (not already indexed)
        Schema::table('vote_tokens', function (Blueprint $table) {
            $table->index(['user_id', 'election_id'], 'vote_tokens_user_election_idx');
            $table->index(['token_hash'], 'vote_tokens_token_hash_idx');
        });

        // Vote authorizations indexes
        Schema::table('vote_authorizations', function (Blueprint $table) {
            $table->index(['election_id', 'expires_at'], 'vote_authorizations_election_expires_idx');
            $table->index(['voter_hash', 'expires_at'], 'vote_authorizations_voter_expires_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vote_records', function (Blueprint $table) {
            $table->dropIndex(['voter_hash']);
            $table->dropIndex(['election_id', 'voter_hash']);
            $table->dropIndex(['voter_hash', 'cast_at']);
        });

        Schema::table('vote_tokens', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'election_id']);
            $table->dropIndex(['token_hash']);
        });

        Schema::table('vote_authorizations', function (Blueprint $table) {
            $table->dropIndex(['election_id', 'expires_at']);
            $table->dropIndex(['voter_hash', 'expires_at']);
        });
    }
};
