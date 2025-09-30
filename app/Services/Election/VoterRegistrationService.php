<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Models\Voting\VoteToken;
use App\Models\User;

class VoterRegistrationService
{
    public function generateVoterRegister(Election $election): array
    {
        // Allow regeneration during republishing (tokens cleared by controller)

        // Get all eligible users at registration time
        $eligibleUsers = User::where('status', 'approved')
            ->whereHas('idDocuments', fn($q) => $q->where('status', 'approved'))
            ->get();

        $tokensCreated = 0;
        $errors = [];

        foreach ($eligibleUsers as $user) {
            try {
                // Create vote token (voter registration)
                VoteToken::create([
                    'user_id' => $user->id,
                    'election_id' => $election->id,
                    'token_id' => $this->generateSecureTokenId($user, $election),
                    'is_used' => false,
                    'issued_at' => now(),
                ]);
                $tokensCreated++;
            } catch (\Exception $e) {
                $errors[] = "Failed to register user {$user->id}: " . $e->getMessage();
            }
        }

        return [
            'total_eligible' => $eligibleUsers->count(),
            'tokens_created' => $tokensCreated,
            'errors' => $errors,
        ];
    }

    private function generateSecureTokenId(User $user, Election $election): string
    {
        return hash('sha256', $user->id . $election->id . $user->email . now()->timestamp);
    }
}