<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Models\Voting\VoteRecord;
use App\Models\Voting\VoteTally;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ElectionTransparencyService
{
    public function generatePublicAuditTrail(Election $election): array
    {
        return [
            'election_metadata' => $this->getElectionMetadata($election),
            'voting_statistics' => $this->getVotingStatistics($election),
            'integrity_proofs' => $this->getIntegrityProofs($election),
            'timeline' => $this->getElectionTimeline($election),
            'public_verification_hash' => $this->generatePublicHash($election)
        ];
    }

    public function getAnonymizedVoteProofs(Election $election): array
    {
        return Cache::remember("vote_proofs_{$election->id}", 3600, function() use ($election) {
            return VoteRecord::where('election_id', $election->id)
                ->select(['receipt_hash', 'cast_at', 'chain_hash'])
                ->orderBy('cast_at')
                ->get()
                ->map(fn($vote) => [
                    'receipt_id' => substr($vote->receipt_hash, 0, 8) . '...' . substr($vote->receipt_hash, -8),
                    'timestamp' => $vote->cast_at->toISOString(),
                    'chain_position' => hash('sha256', $vote->chain_hash)
                ])
                ->toArray();
        });
    }

    private function getElectionMetadata(Election $election): array
    {
        return [
            'title' => $election->title,
            'type' => $election->type->value,
            'start_time' => $election->starts_at->toISOString(),
            'end_time' => $election->ends_at->toISOString(),
            'total_positions' => $election->positions()->count(),
            'total_candidates' => $election->candidates()->count(),
            'eligible_voters' => $election->voteTokens()->count()
        ];
    }

    private function getVotingStatistics(Election $election): array
    {
        $totalEligible = $election->voteTokens()->count();
        $totalVoted = $election->voteTokens()->where('is_used', true)->count();
        
        return [
            'total_eligible_voters' => $totalEligible,
            'total_votes_cast' => $totalVoted,
            'turnout_percentage' => $totalEligible > 0 ? round(($totalVoted / $totalEligible) * 100, 2) : 0,
            'votes_by_hour' => $this->getVotesByHour($election),
            'participation_rate' => $this->calculateParticipationRate($election)
        ];
    }

    private function getIntegrityProofs(Election $election): array
    {
        $integrityService = app(ElectionIntegrityService::class);
        return $integrityService->verifyElectionIntegrity($election);
    }

    private function getElectionTimeline(Election $election): array
    {
        return [
            'election_created' => $election->created_at->toISOString(),
            'voting_started' => $election->starts_at->toISOString(),
            'voting_ended' => $election->ends_at->toISOString(),
            'results_published' => $election->results_published ? now()->toISOString() : null,
            'total_duration_hours' => $election->starts_at->diffInHours($election->ends_at)
        ];
    }

    private function getVotesByHour(Election $election): array
    {
        $results = VoteRecord::where('election_id', $election->id)
            ->selectRaw('HOUR(cast_at) as hour, COUNT(*) as count')
            ->groupByRaw('HOUR(cast_at)')
            ->orderByRaw('HOUR(cast_at)')
            ->get();
        
        return $results->pluck('count', 'hour')->toArray();
    }

    private function calculateParticipationRate(Election $election): string
    {
        $totalEligible = $election->voteTokens()->count();
        $totalVoted = $election->voteTokens()->where('is_used', true)->count();
        
        if ($totalEligible === 0) return 'No eligible voters';
        
        $rate = ($totalVoted / $totalEligible) * 100;
        
        return match(true) {
            $rate >= 75 => 'Excellent',
            $rate >= 50 => 'Good', 
            $rate >= 25 => 'Fair',
            default => 'Low'
        };
    }

    private function generatePublicHash(Election $election): string
    {
        $data = [
            'election_id' => $election->id,
            'total_votes' => VoteRecord::where('election_id', $election->id)->count(),
            'vote_hashes' => VoteRecord::where('election_id', $election->id)->pluck('voter_hash')->sort()->values(),
            'tally_hashes' => VoteTally::where('election_id', '=', $election->id)->pluck('tally_hash')->sort()->values()
        ];
        
        return hash('sha256', json_encode($data));
    }
}
