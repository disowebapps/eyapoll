<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, expand the enum to include new values and existing 'draft'
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('draft', 'scheduled', 'active', 'ended', 'cancelled', 'upcoming', 'ongoing', 'completed', 'archived') NOT NULL DEFAULT 'upcoming'");

        // Update existing election statuses to new flow
        DB::table('elections')->where('status', 'draft')->update(['status' => 'upcoming']);
        DB::table('elections')->where('status', 'scheduled')->update(['status' => 'upcoming']);
        DB::table('elections')->where('status', 'active')->update(['status' => 'ongoing']);
        DB::table('elections')->where('status', 'ended')->update(['status' => 'completed']);

        // Note: Auto-archiving will be done later when results_published column is added

        // Now set the enum to the new values only
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('upcoming', 'ongoing', 'completed', 'archived', 'cancelled') NOT NULL DEFAULT 'upcoming'");
    }

    public function down(): void
    {
        DB::transaction(function () {
            // Revert to legacy status names
            DB::table('elections')->where('status', 'upcoming')->update(['status' => 'scheduled']);
            DB::table('elections')->where('status', 'ongoing')->update(['status' => 'active']);
            DB::table('elections')->whereIn('status', ['completed', 'archived'])->update(['status' => 'ended']);
        });
    }
};