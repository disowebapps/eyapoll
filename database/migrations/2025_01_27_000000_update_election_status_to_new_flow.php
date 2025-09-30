<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skipped to avoid order issue
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