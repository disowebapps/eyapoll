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
        // Skip if table doesn't exist (will be created by earlier migration)
        if (!Schema::hasTable('system_settings')) {
            return;
        }
        
        // Insert default voter registration setting if not exists
        // Skipped due to no users existing yet
        /*
        $exists = DB::table('system_settings')
            ->where('key', 'voter_registration_enabled')
            ->exists();

        if (!$exists) {
            try {
                DB::table('system_settings')->insert([
                    'key' => 'voter_registration_enabled',
                    'value' => 'true',
                    'description' => 'Controls whether voter registration is enabled system-wide',
                    'updated_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (\Exception $e) {
                // Fallback for different table structures
                DB::table('system_settings')->insert([
                    'key' => 'voter_registration_enabled',
                    'value' => 'true',
                    'description' => 'Controls whether voter registration is enabled system-wide',
                    'updated_by' => 1
                ]);
            }
        }
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop the specific setting, not the entire table
        DB::table('system_settings')
            ->where('key', 'voter_registration_enabled')
            ->delete();
    }
};
