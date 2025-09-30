<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observer_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observer_id')->constrained('observers')->onDelete('cascade');
            $table->foreignId('election_id')->nullable()->constrained('elections')->onDelete('cascade');
            $table->enum('type', ['security', 'irregularity', 'technical', 'audit', 'other']);
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['active', 'investigating', 'resolved', 'dismissed']);
            $table->string('title');
            $table->text('description');
            $table->json('evidence')->nullable();
            $table->timestamp('occurred_at');
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'severity']);
            $table->index(['election_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observer_alerts');
    }
};