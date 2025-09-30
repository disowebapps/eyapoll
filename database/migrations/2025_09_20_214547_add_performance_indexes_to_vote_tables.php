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
        Schema::table('vote_tallies', function (Blueprint $table) {
            // Composite indexes for result queries
            $table->index(['election_id', 'position_id'], 'vote_tallies_election_position_idx');
            $table->index(['position_id', 'vote_count'], 'vote_tallies_position_votes_idx');
            $table->index(['election_id', 'last_updated'], 'vote_tallies_election_updated_idx');
            $table->index(['candidate_id', 'vote_count'], 'vote_tallies_candidate_votes_idx');
        });

        Schema::table('votes', function (Blueprint $table) {
            // Additional indexes for result aggregation queries
            $table->index(['election_id', 'position_id'], 'votes_election_position_idx');
            $table->index(['position_id', 'cast_at'], 'votes_position_cast_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vote_tallies', function (Blueprint $table) {
            $table->dropIndex('vote_tallies_election_position_idx');
            $table->dropIndex('vote_tallies_position_votes_idx');
            $table->dropIndex('vote_tallies_election_updated_idx');
            $table->dropIndex('vote_tallies_candidate_votes_idx');
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->dropIndex('votes_election_position_idx');
            $table->dropIndex('votes_position_cast_idx');
        });
    }
};
