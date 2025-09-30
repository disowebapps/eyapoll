<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->json('step1_data')->nullable();
            $table->json('step2_data')->nullable();
            $table->json('step3_data')->nullable();
            $table->integer('current_step')->default(1);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index(['session_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_sessions');
    }
};