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
        Schema::create('notification_queues', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->string('channel'); // email, sms, in_app, push
            $table->unsignedBigInteger('recipient_id')->nullable(); // User ID for in-app notifications
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->integer('max_retries')->default(3);
            $table->integer('retry_count')->default(0);
            $table->timestamp('available_at')->nullable(); // For delayed sending
            $table->timestamp('reserved_at')->nullable(); // When job was picked up
            $table->string('reserved_by')->nullable(); // Which worker picked it up
            $table->json('payload')->nullable(); // Additional data for the job
            $table->text('error_message')->nullable(); // Last error if any
            $table->timestamp('failed_at')->nullable(); // When it failed permanently
            $table->timestamps();

            // Indexes for queue performance
            $table->index(['channel', 'available_at']);
            $table->index(['priority', 'available_at']);
            $table->index('reserved_at');
            $table->index('failed_at');
            $table->index(['notification_id', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_queues');
    }
};
