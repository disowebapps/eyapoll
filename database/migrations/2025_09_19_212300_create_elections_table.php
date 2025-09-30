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
        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['general', 'bye', 'constitutional', 'opinion']);
            $table->enum('status', ['draft', 'active', 'ended', 'cancelled'])->default('draft');
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->json('settings')->default('{}');
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('chain_hash')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['status']);
            $table->index(['type']);
            $table->index(['starts_at', 'ends_at']);
            $table->index(['status', 'starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elections');
    }
};
