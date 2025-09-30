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
        // Update id_documents table
        Schema::table('id_documents', function (Blueprint $table) {
            // Update document_type enum to include new types
            $table->enum('document_type', [
                'national_id',
                'passport',
                'international_passport',
                'drivers_license',
                'drivers_license_provisional',
                'drivers_license_full',
                'visa',
                'residence_permit',
                'birth_certificate',
                'marriage_certificate',
                'utility_bill',
                'bank_statement'
            ])->change();

            // Add versioning fields
            $table->integer('version_number')->default(1)->after('document_type');
            $table->foreignId('parent_id')->nullable()->constrained('id_documents')->onDelete('set null')->after('version_number');

            // Add expiry and international support
            $table->date('expiry_date')->nullable()->after('parent_id');
            $table->string('country_code', 3)->nullable()->after('expiry_date'); // ISO 3166-1 alpha-3

            // Indexes
            $table->index(['user_id', 'document_type', 'version_number']);
            $table->index(['expiry_date']);
            $table->index(['country_code']);
        });

        // Update candidate_documents table
        Schema::table('candidate_documents', function (Blueprint $table) {
            // Add versioning fields
            $table->integer('version_number')->default(1)->after('document_type');
            $table->foreignId('parent_id')->nullable()->constrained('candidate_documents')->onDelete('set null')->after('version_number');

            // Add expiry and international support
            $table->date('expiry_date')->nullable()->after('parent_id');
            $table->string('country_code', 3)->nullable()->after('expiry_date'); // ISO 3166-1 alpha-3

            // Indexes
            $table->index(['candidate_id', 'document_type', 'version_number'], 'candidate_docs_version_idx');
            $table->index(['expiry_date']);
            $table->index(['country_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse id_documents changes
        Schema::table('id_documents', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'document_type', 'version_number']);
            $table->dropIndex(['expiry_date']);
            $table->dropIndex(['country_code']);

            $table->dropForeign(['parent_id']);
            $table->dropColumn(['version_number', 'parent_id', 'expiry_date', 'country_code']);

            // Revert document_type enum
            $table->enum('document_type', ['national_id', 'passport', 'drivers_license'])->change();
        });

        // Reverse candidate_documents changes
        Schema::table('candidate_documents', function (Blueprint $table) {
            $table->dropIndex('candidate_docs_version_idx');
            $table->dropIndex(['expiry_date']);
            $table->dropIndex(['country_code']);

            $table->dropForeign(['parent_id']);
            $table->dropColumn(['version_number', 'parent_id', 'expiry_date', 'country_code']);
        });
    }
};
