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
        // First update the status enum to include new values
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending', 'review', 'approved', 'accredited', 'rejected', 'suspended', 'temporary_hold', 'expired', 'renewal_required') NOT NULL DEFAULT 'pending'");

        // Add new metadata fields
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('hold_until')->nullable()->after('suspension_reason');
            $table->timestamp('expiry_date')->nullable()->after('hold_until');
            $table->timestamp('renewal_deadline')->nullable()->after('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the new fields
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['hold_until', 'expiry_date', 'renewal_deadline']);
        });

        // Revert the status enum
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending', 'review', 'approved', 'accredited', 'rejected', 'suspended') NOT NULL DEFAULT 'pending'");
    }
};