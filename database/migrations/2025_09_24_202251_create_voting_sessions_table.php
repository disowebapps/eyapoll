<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voting_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('voter_hash');
            $table->unsignedBigInteger('election_id');
            $table->json('selections')->nullable();
            $table->integer('current_position_index')->default(0);
            $table->json('progress')->nullable();
            $table->timestamp('last_activity_at');
            $table->timestamps();
            
            $table->index(['voter_hash', 'election_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voting_sessions');
    }
};