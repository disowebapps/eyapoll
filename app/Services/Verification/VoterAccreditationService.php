<?php

namespace App\Services;

use App\Models\User;
use App\Models\Election\Election;
use App\Enums\Auth\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoterAccreditationService
{
    /**
     * Accredit verified users for an election by distributing vote tokens
     */
    public function accreditVotersForElection(Election $election, $admin): array
    {
        return DB::transaction(function () use ($election, $admin) {
            // Get all APPROVED (verified) users who don't have tokens for this election
            $verifiedUsers = User::where('status', UserStatus::APPROVED)
                ->whereDoesntHave('voteTokens', function ($query) use ($election) {
                    $query->where('election_id', $election->id);
                })
                ->get();

            $accreditedCount = 0;
            
            foreach ($verifiedUsers as $user) {
                // Create vote token
                $user->voteTokens()->create([
                    'election_id' => $election->id,
                    'token' => $this->generateVoteToken($user, $election),
                    'is_used' => false,
                    'issued_at' => now(),
                ]);
                
                // Transition user to ACCREDITED status
                $user->update(['status' => UserStatus::ACCREDITED]);
                
                $accreditedCount++;
                
                Log::info('User accredited for voting', [
                    'user_id' => $user->id,
                    'election_id' => $election->id,
                    'admin_id' => $admin->id
                ]);
            }
            
            return [
                'accredited_count' => $accreditedCount,
                'total_verified' => $verifiedUsers->count()
            ];
        });
    }
    
    private function generateVoteToken(User $user, Election $election): string
    {
        return app(\App\Services\Cryptographic\CryptographicService::class)
            ->generateVoteToken($user->id, $election->id);
    }
}