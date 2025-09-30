<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Models\Voting\VoteRecord;
use App\Models\Voting\VoteTally;
use Illuminate\Support\Facades\Cache;

class ElectionIntegrityService
{
    public function verifyElectionIntegrity(Election $election): array
    {
        return Cache::remember("election_integrity_{$election->id}", 300, function() use ($election) {
            return [
                'chain_verified' => $this->verifyVoteChain($election),
                'tallies_verified' => $this->verifyTallies($election),
                'token_integrity' => $this->verifyTokenIntegrity($election),
                'timestamp_integrity' => $this->verifyTimestamps($election),
                'last_verification' => now()->toISOString(),
                'integrity_score' => $this->calculateIntegrityScore($election)
            ];
        });
    }

    private function verifyVoteChain(Election $election): bool
    {
        $votes = VoteRecord::where('election_id', $election->id)
            ->orderBy('cast_at')
            ->get(['chain_hash', 'previous_hash']);

        if ($votes->isEmpty()) return true;

        $previousHash = null;
        foreach ($votes as $vote) {
            if ($vote->previous_hash !== $previousHash) {
                return false;
            }
            $previousHash = $vote->chain_hash;
        }

        return true;
    }

    private function verifyTallies(Election $election): array
    {
        $tallies = VoteTally::where('election_id', $election->id)->get();
        $verified = 0;
        $total = $tallies->count();

        foreach ($tallies as $tally) {
            if ($this->verifyTallyIntegrity($tally)) {
                $verified++;
            }
        }

        return [
            'verified_count' => $verified,
            'total_count' => $total,
            'percentage' => $total > 0 ? round(($verified / $total) * 100, 2) : 100
        ];
    }

    private function verifyTallyIntegrity(VoteTally $tally): bool
    {
        // Verify tally hash matches calculated hash
        $calculatedHash = hash('sha256', json_encode([
            'election_id' => $tally->election_id,
            'position_id' => $tally->position_id,
            'candidate_id' => $tally->candidate_id,
            'vote_count' => $tally->vote_count
        ]));

        return $tally->tally_hash === $calculatedHash;
    }

    private function verifyTokenIntegrity(Election $election): bool
    {
        $totalTokens = $election->voteTokens()->count();
        $usedTokens = $election->voteTokens()->where('is_used', true)->count();
        $totalVotes = $election->votes()->count();

        // Used tokens should match total votes
        return $usedTokens === $totalVotes;
    }

    private function verifyTimestamps(Election $election): bool
    {
        $votes = VoteRecord::where('election_id', $election->id)
            ->where(function($query) use ($election) {
                $query->where('cast_at', '<', $election->starts_at)
                      ->orWhere('cast_at', '>', $election->ends_at);
            })
            ->exists();

        // No votes should exist outside election period
        return !$votes;
    }

    private function calculateIntegrityScore(Election $election): float
    {
        $checks = [
            'chain' => $this->verifyVoteChain($election),
            'tokens' => $this->verifyTokenIntegrity($election),
            'timestamps' => $this->verifyTimestamps($election)
        ];

        $tallyVerification = $this->verifyTallies($election);
        $checks['tallies'] = $tallyVerification['percentage'] >= 95;

        $passed = array_sum($checks);
        $total = count($checks);

        return round(($passed / $total) * 100, 2);
    }
}