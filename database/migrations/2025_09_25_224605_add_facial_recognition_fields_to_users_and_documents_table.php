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
        // Add facial recognition fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->text('face_descriptor')->nullable(); // Face-api.js face descriptor
            $table->string('face_image_path')->nullable(); // Path to face image
            $table->decimal('face_match_score', 5, 4)->nullable(); // Match confidence score
            $table->timestamp('face_verified_at')->nullable();
            $table->json('face_verification_data')->nullable(); // Additional face data
        });

        // Add facial recognition fields to id_documents table
        Schema::table('id_documents', function (Blueprint $table) {
            $table->text('face_descriptor')->nullable(); // Face descriptor from ID document
            $table->decimal('face_match_score', 5, 4)->nullable(); // Match score with user photo
            $table->timestamp('face_matched_at')->nullable();
            $table->json('face_match_data')->nullable(); // Face matching details
            $table->string('document_category')->nullable(); // Auto-classified category
            $table->decimal('classification_confidence', 5, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'face_descriptor',
                'face_image_path',
                'face_match_score',
                'face_verified_at',
                'face_verification_data'
            ]);
        });

        Schema::table('id_documents', function (Blueprint $table) {
            $table->dropColumn([
                'face_descriptor',
                'face_match_score',
                'face_matched_at',
                'face_match_data',
                'document_category',
                'classification_confidence'
            ]);
        });
    }
};
