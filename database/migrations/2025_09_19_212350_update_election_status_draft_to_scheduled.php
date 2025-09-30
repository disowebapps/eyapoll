<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, update the enum to include both values
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('draft', 'scheduled', 'active', 'ended', 'cancelled') NOT NULL DEFAULT 'draft'");
        
        // Update existing 'draft' status to 'scheduled'
        DB::table('elections')
            ->where('status', 'draft')
            ->update(['status' => 'scheduled']);
            
        // Remove 'draft' from enum
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('scheduled', 'active', 'ended', 'cancelled') NOT NULL DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        // Revert 'scheduled' back to 'draft'
        DB::table('elections')
            ->where('status', 'scheduled')
            ->update(['status' => 'draft']);
            
        // Revert the enum constraint
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('draft', 'active', 'ended', 'cancelled') NOT NULL DEFAULT 'draft'");
    }
};