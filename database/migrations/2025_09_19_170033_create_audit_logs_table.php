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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // null for system actions
            $table->string('action'); // vote_cast, user_approved, election_started, etc.
            $table->string('entity_type')->nullable(); // User, Election, Vote, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable(); // Previous state
            $table->json('new_values')->nullable(); // New state
            $table->string('ip_address', 45); // IPv4 or IPv6
            $table->text('user_agent')->nullable();
            $table->string('integrity_hash'); // Chain hash for immutability
            $table->string('previous_hash')->nullable(); // Links to previous log
            $table->timestamps();

            // Indexes for performance and integrity
            $table->index(['user_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['action', 'created_at']);
            $table->index(['integrity_hash']);
            $table->index(['previous_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
