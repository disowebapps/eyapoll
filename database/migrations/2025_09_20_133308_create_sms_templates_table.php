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
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->unique(); // UserApproved, VoteCast, etc
            $table->text('message_template');
            $table->integer('max_length')->default(160); // SMS character limit
            $table->json('variables')->default('[]'); // Available template variables
            $table->string('from_number')->nullable(); // SMS sender number
            $table->decimal('estimated_cost', 8, 4)->default(0.01); // Cost per SMS
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
        Schema::dropIfExists('sms_templates');
    }
};
