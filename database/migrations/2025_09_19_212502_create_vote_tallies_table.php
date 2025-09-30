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
        Schema::create('vote_tallies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->foreignId('position_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidate_id')->nullable()->constrained()->onDelete('cascade'); // null for abstentions
            $table->integer('vote_count')->default(0);
            $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate();
            $table->string('tally_hash'); // For integrity verification
            $table->timestamps();

            // Unique constraint to prevent duplicate tallies
            $table->unique(['election_id', 'position_id', 'candidate_id']);

            // Indexes for performance
            $table->index(['election_id']);
            $table->index(['position_id']);
            $table->index(['candidate_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_tallies');
    }
};
