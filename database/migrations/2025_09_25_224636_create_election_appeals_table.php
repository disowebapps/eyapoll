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
        Schema::create('election_appeals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('election_id')->constrained('elections')->onDelete('cascade');
            $table->foreignId('appellant_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['result_irregularity', 'procedural_error', 'technical_issue', 'voter_fraud', 'system_error']);
            $table->enum('status', ['submitted', 'under_review', 'approved', 'rejected', 'dismissed'])->default('submitted');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('title');
            $table->text('description');
            $table->json('appeal_data')->nullable(); // Additional structured data
            $table->string('integrity_hash')->nullable(); // Cryptographic integrity
            $table->string('previous_hash')->nullable(); // Hash chain
            $table->timestamp('submitted_at');
            $table->timestamp('deadline_at')->nullable(); // Appeal deadline
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->json('escalation_history')->nullable(); // Track escalations
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['election_id']);
            $table->index(['appellant_id']);
            $table->index(['status']);
            $table->index(['priority']);
            $table->index(['type']);
            $table->index(['assigned_to']);
            $table->index(['submitted_at']);
            $table->index(['deadline_at']);
            $table->index(['status', 'priority']);
            $table->index(['election_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_appeals');
    }
};
