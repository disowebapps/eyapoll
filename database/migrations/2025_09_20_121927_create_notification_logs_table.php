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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->string('channel'); // email, sms, in_app, push
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('status'); // pending, sent, delivered, failed, bounced
            $table->text('message')->nullable(); // The actual message content
            $table->text('error_message')->nullable(); // Error details if failed
            $table->json('metadata')->nullable(); // Additional provider-specific data
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->string('provider_response')->nullable(); // Raw response from provider
            $table->timestamps();

            // Indexes for performance
            $table->index(['notification_id', 'channel']);
            $table->index(['status', 'created_at']);
            $table->index(['channel', 'status']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
