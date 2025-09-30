<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Election\Election;

class VoterRegistrationService
{
    public static function isEnabled(): bool
    {
        // Check if any active election has published voter register
        $activeElection = Election::whereNotNull('voter_register_published')
            ->whereIn('phase', ['verification', 'voting', 'collation'])
            ->exists();

        if ($activeElection) {
            return false;
        }

        $setting = DB::table('system_settings')
            ->where('key', 'voter_registration_enabled')
            ->first();

        return $setting ? $setting->value === 'true' : true;
    }

    public static function pause(Election $election): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'voter_registration_enabled'],
            ['value' => 'false', 'updated_at' => now()]
        );

        Log::info('Voter registration paused', [
            'election_id' => $election->id,
            'election_title' => $election->title,
            'admin_id' => auth('admin')->id()
        ]);
    }

    public static function resume(Election $election): void
    {
        // Only resume if no other elections have published registers
        $otherActiveElections = Election::where('id', '!=', $election->id)
            ->whereNotNull('voter_register_published')
            ->whereIn('phase', ['verification', 'voting', 'collation'])
            ->exists();

        if (!$otherActiveElections) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => 'voter_registration_enabled'],
                ['value' => 'true', 'updated_at' => now()]
            );

            Log::info('Voter registration resumed', [
                'election_id' => $election->id,
                'election_title' => $election->title
            ]);
        }
    }
}