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
        // First, expand the enum to include both old and new values to avoid truncation errors
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('scheduled', 'active', 'ended', 'cancelled', 'upcoming', 'ongoing', 'completed', 'archived') NOT NULL DEFAULT 'upcoming'");

        // Update any remaining legacy status values to new ones
        DB::table('elections')->where('status', 'scheduled')->update(['status' => 'upcoming']);
        DB::table('elections')->where('status', 'active')->update(['status' => 'ongoing']);
        DB::table('elections')->where('status', 'ended')->update(['status' => 'completed']);

        // Now set the enum to the new values only
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('upcoming', 'ongoing', 'completed', 'archived', 'cancelled') NOT NULL DEFAULT 'upcoming'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Expand enum back to include old values
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('scheduled', 'active', 'ended', 'cancelled', 'upcoming', 'ongoing', 'completed', 'archived') NOT NULL DEFAULT 'scheduled'");

        // Revert to legacy status names
        DB::table('elections')->where('status', 'upcoming')->update(['status' => 'scheduled']);
        DB::table('elections')->where('status', 'ongoing')->update(['status' => 'active']);
        DB::table('elections')->whereIn('status', ['completed', 'archived'])->update(['status' => 'ended']);

        // Set enum back to old values
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('scheduled', 'active', 'ended', 'cancelled') NOT NULL DEFAULT 'scheduled'");
    }
};
