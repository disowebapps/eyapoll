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
        Schema::table('users', function (Blueprint $table) {
            // Risk scoring and compliance fields
            $table->decimal('risk_score', 5, 4)->default(0.5); // 0-1 risk score
            $table->json('risk_factors')->nullable(); // Array of risk factors
            $table->timestamp('risk_assessed_at')->nullable();
            $table->string('risk_level')->default('medium'); // low, medium, high, critical

            // AML/KYC compliance
            $table->boolean('aml_screened')->default(false);
            $table->timestamp('aml_screened_at')->nullable();
            $table->json('aml_results')->nullable();
            $table->string('compliance_status')->default('pending'); // pending, cleared, flagged, rejected

            // Address verification
            $table->boolean('address_verified')->default(false);
            $table->timestamp('address_verified_at')->nullable();
            $table->json('address_verification_data')->nullable();
            $table->string('address_verification_provider')->nullable();

            // Background check
            $table->boolean('background_check_completed')->default(false);
            $table->timestamp('background_check_at')->nullable();
            $table->json('background_check_results')->nullable();
            $table->string('background_check_provider')->nullable();
            $table->string('background_check_status')->default('not_started'); // not_started, in_progress, completed, failed

            // Data retention
            $table->timestamp('data_retention_until')->nullable();
            $table->string('data_retention_policy')->default('standard'); // standard, extended, permanent
            $table->timestamp('gdpr_deletion_requested_at')->nullable();
            $table->timestamp('gdpr_export_requested_at')->nullable();
            $table->json('gdpr_requests')->nullable(); // Track all GDPR requests

            // Additional verification fields
            $table->json('verification_history')->nullable(); // History of verification attempts
            $table->integer('verification_attempts')->default(0);
            $table->timestamp('last_verification_attempt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'risk_score',
                'risk_factors',
                'risk_assessed_at',
                'risk_level',
                'aml_screened',
                'aml_screened_at',
                'aml_results',
                'compliance_status',
                'address_verified',
                'address_verified_at',
                'address_verification_data',
                'address_verification_provider',
                'background_check_completed',
                'background_check_at',
                'background_check_results',
                'background_check_provider',
                'background_check_status',
                'data_retention_until',
                'data_retention_policy',
                'gdpr_deletion_requested_at',
                'gdpr_export_requested_at',
                'gdpr_requests',
                'verification_history',
                'verification_attempts',
                'last_verification_attempt'
            ]);
        });
    }
};
