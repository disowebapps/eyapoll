<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('elections')) {
            return;
        }

        // Update legacy status values to new enum values
        DB::table('elections')->where('status', 'scheduled')->update(['status' => 'upcoming']);
        DB::table('elections')->where('status', 'active')->update(['status' => 'ongoing']);
        DB::table('elections')->where('status', 'ended')->update(['status' => 'completed']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('elections')) {
            return;
        }

        // Revert to legacy values
        DB::table('elections')->where('status', 'upcoming')->update(['status' => 'scheduled']);
        DB::table('elections')->where('status', 'ongoing')->update(['status' => 'active']);
        DB::table('elections')->where('status', 'completed')->update(['status' => 'ended']);
    }
};