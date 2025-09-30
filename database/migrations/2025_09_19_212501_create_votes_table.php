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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            // NO user_id - this ensures ballot secrecy
            $table->string('vote_hash')->unique(); // Anonymous ballot identifier
            $table->foreignId('election_id')->constrained()->onDelete('restrict');
            $table->foreignId('position_id')->constrained()->onDelete('restrict');
            $table->text('ballot_data'); // Encrypted candidate selections
            $table->string('receipt_hash')->unique(); // For voter verification
            $table->string('chain_hash'); // Links to previous vote for integrity
            $table->string('integrity_signature'); // Digital signature
            $table->timestamp('cast_at')->useCurrent();
            $table->string('ip_hash'); // Hashed IP for audit without privacy violation

            // Indexes for performance
            $table->index(['election_id', 'cast_at']);
            $table->index(['position_id']);
            $table->index(['chain_hash']);
            $table->index(['receipt_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
