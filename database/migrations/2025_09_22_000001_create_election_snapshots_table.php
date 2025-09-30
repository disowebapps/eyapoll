<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->datetime('snapshot_at');
            $table->string('hash', 64);
            $table->timestamps();
            
            $table->index(['election_id', 'snapshot_at']);
            $table->unique(['election_id', 'hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_snapshots');
    }
};