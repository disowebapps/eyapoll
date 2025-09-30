<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vote_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->string('voter_hash')->index();
            $table->text('encrypted_selections');
            $table->string('receipt_hash')->unique();
            $table->string('chain_hash')->nullable();
            $table->string('previous_hash')->nullable();
            $table->timestamp('cast_at');
            $table->json('verification_data')->nullable();
            $table->timestamps();
            
            $table->index(['election_id', 'cast_at']);
            $table->index(['voter_hash', 'election_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vote_records');
    }
};