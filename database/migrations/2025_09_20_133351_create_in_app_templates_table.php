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
        Schema::create('in_app_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->unique(); // UserApproved, VoteCast, etc
            $table->string('title');
            $table->text('message_template');
            $table->string('icon')->nullable(); // Heroicon name for notification
            $table->string('action_url')->nullable(); // URL to redirect to when clicked
            $table->string('action_text')->nullable(); // Button text for action
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->integer('retention_days')->default(30); // How long to keep the notification
            $table->json('variables')->default('[]'); // Available template variables
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            // Index for performance
            $table->index(['event_type', 'is_active']);
            $table->index(['priority', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('in_app_templates');
    }
};
