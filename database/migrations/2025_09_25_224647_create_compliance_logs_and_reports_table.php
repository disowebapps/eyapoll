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
        // Compliance logs table for tracking all compliance-related activities
        Schema::create('compliance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // aml_check, risk_assessment, document_verification, etc.
            $table->string('event_subtype')->nullable();
            $table->json('event_data')->nullable();
            $table->string('severity')->default('info'); // info, warning, error, critical
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['user_id', 'event_type']);
            $table->index(['created_at']);
            $table->index(['severity']);
        });

        // Regulatory reports table for compliance reporting
        Schema::create('regulatory_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type'); // aml_sar, kyc_summary, risk_report, etc.
            $table->string('report_period')->nullable(); // monthly, quarterly, annual
            $table->date('report_date');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->json('report_data');
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected
            $table->text('notes')->nullable();
            $table->string('file_path')->nullable(); // Path to generated report file
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['report_type', 'report_date']);
            $table->index(['status']);
        });

        // Data retention policies table
        Schema::create('data_retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_name');
            $table->string('policy_type'); // user_data, documents, logs, etc.
            $table->integer('retention_days');
            $table->text('description')->nullable();
            $table->boolean('auto_delete')->default(true);
            $table->json('conditions')->nullable(); // Additional conditions for retention
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['policy_name', 'policy_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_retention_policies');
        Schema::dropIfExists('regulatory_reports');
        Schema::dropIfExists('compliance_logs');
    }
};
