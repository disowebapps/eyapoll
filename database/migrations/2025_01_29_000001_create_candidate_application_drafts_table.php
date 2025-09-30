<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('candidate_application_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('election_id')->constrained('elections')->onDelete('cascade');
            $table->json('form_data');
            $table->integer('current_step')->default(1);
            $table->timestamps();
            
            $table->unique(['user_id', 'election_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('candidate_application_drafts');
    }
};