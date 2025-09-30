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
        Schema::create('consensus_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('action_type'); // start_election, end_election, approve_candidate
            $table->unsignedBigInteger('target_id'); // ID of target entity
            $table->string('target_type'); // Election, Candidate, etc
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->integer('required_approvals');
            $table->integer('current_approvals')->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->json('approval_data')->default('{}'); // Who approved, when
            $table->timestamp('expires_at');
            $table->timestamps();

            // Indexes for performance
            $table->index(['action_type', 'target_id', 'target_type']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consensus_approvals');
    }
};
