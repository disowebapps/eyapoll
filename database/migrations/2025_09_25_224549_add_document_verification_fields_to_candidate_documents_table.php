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
        Schema::table('candidate_documents', function (Blueprint $table) {
            $table->string('perceptual_hash')->nullable()->after('file_hash');
            $table->text('ocr_text')->nullable()->after('perceptual_hash');
            $table->decimal('authenticity_score', 5, 2)->nullable()->after('ocr_text'); // 0-100
            $table->enum('verification_status', ['pending', 'passed', 'failed'])->default('pending')->after('authenticity_score');
            $table->json('verification_errors')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('verification_errors');

            $table->index(['perceptual_hash']);
            $table->index(['verification_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_documents', function (Blueprint $table) {
            $table->dropIndex(['perceptual_hash']);
            $table->dropIndex(['verification_status']);
            $table->dropColumn(['perceptual_hash', 'ocr_text', 'authenticity_score', 'verification_status', 'verification_errors', 'verified_at']);
        });
    }
};
