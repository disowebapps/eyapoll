<?php

namespace App\Services\Voting;

use App\Models\User;
use App\Models\Election\Election;
use Illuminate\Support\Facades\Cache;

class VoterHashService
{
    public function generateVoterHash(User $user, Election $election): string
    {
        $cacheKey = "voter_hash_{$user->id}_{$election->id}";

        return Cache::remember($cacheKey, 3600, function() use ($user, $election) {
            return hash('sha256', $user->id . $election->id . config('app.key'));
        });
    }

    public function getUserByHash(string $voterHash, int $electionId): ?User
    {
        // This is a simplified implementation
        // In production, you'd need a more secure reverse lookup
        return null;
    }

    public function checkTokenExists(string $tokenHash): bool
    {
        $cacheKey = "token_exists_{$tokenHash}";

        return Cache::remember($cacheKey, 300, function() use ($tokenHash) {
            return \App\Models\Voting\VoteToken::where('token_hash', $tokenHash)->exists();
        });
    }
}