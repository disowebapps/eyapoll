<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the document_type enum to include all DocumentType enum values
        DB::statement("ALTER TABLE id_documents MODIFY COLUMN document_type ENUM(
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
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original enum values
        DB::statement("ALTER TABLE id_documents MODIFY COLUMN document_type ENUM(
            'national_id',
            'passport',
            'drivers_license'
        ) NOT NULL");
    }
};
