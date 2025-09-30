<?php

namespace App\Services;

use App\Models\Election\Election;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmergencyElectionService
{
    public function emergencyHalt(Election $election, Admin $admin, string $reason): bool
    {
        return DB::transaction(function () use ($election, $admin, $reason) {
            $election->update([
                'status' => 'emergency_suspended',
                'voting_closed' => true
            ]);
            
            Log::critical('EMERGENCY ELECTION HALT', [
                'election_id' => $election->id,
                'admin_id' => $admin->id,
                'reason' => $reason,
                'timestamp' => now()
            ]);
            
            return true;
        });
    }
    
    public function revokeUserTokens(int $userId, string $reason): int
    {
        return DB::transaction(function () use ($userId, $reason) {
            $tokensRevoked = DB::table('vote_tokens')
                ->where('user_id', $userId)
                ->where('is_used', false)
                ->update(['is_used' => true, 'used_at' => now()]);
                
            Log::warning('Vote tokens revoked', [
                'user_id' => $userId,
                'tokens_revoked' => $tokensRevoked,
                'reason' => $reason
            ]);
            
            return $tokensRevoked;
        });
    }
}