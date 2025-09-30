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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->unique(); // UserApproved, VoteCast, etc
            $table->string('subject');
            $table->text('body_template');
            $table->text('html_template')->nullable(); // For rich HTML emails
            $table->json('variables')->default('[]'); // Available template variables
            $table->string('from_name')->default('AYApoll');
            $table->string('from_email')->default('noreply@ayapoll.org');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            // Index for performance
            $table->index(['event_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
