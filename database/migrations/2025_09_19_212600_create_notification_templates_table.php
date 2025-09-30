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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // UserApproved, VoteCast, etc
            $table->enum('channel', ['email', 'sms', 'in_app']);
            $table->string('subject')->nullable(); // nullable for SMS
            $table->text('body_template');
            $table->json('variables')->default('[]'); // Available template variables
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('admins')->onDelete('restrict');
            $table->timestamps();

            // Unique constraint for event_type + channel combination
            $table->unique(['event_type', 'channel']);

            // Index for performance
            $table->index(['event_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
