<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observer_elections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observer_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['observer_id', 'election_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observer_elections');
    }
};