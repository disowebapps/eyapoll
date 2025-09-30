<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vote_authorizations', function (Blueprint $table) {
            $table->id();
            $table->string('voter_hash')->index();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->string('auth_token')->unique();
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->integer('extension_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->integer('initial_timeout_minutes');
            $table->json('eligibility_snapshot')->nullable();
            $table->timestamps();
            
            $table->index(['voter_hash', 'election_id']);
            $table->index(['expires_at', 'is_used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vote_authorizations');
    }
};