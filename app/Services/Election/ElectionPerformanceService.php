<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ElectionPerformanceService
{
    public function optimizeElectionQueries(Election $election): void
    {
        // Preload and cache critical election data
        $this->preloadElectionData($election);
        $this->optimizeResultsCache($election);
    }

    public function getOptimizedResults(Election $election): array
    {
        return Cache::remember("optimized_results_{$election->id}", 600, function() use ($election) {
            return DB::select("
                SELECT 
                    p.title as position_title,
                    c.first_name,
                    c.last_name,
                    vt.vote_count,
                    ROUND((vt.vote_count * 100.0 / SUM(vt.vote_count) OVER (PARTITION BY p.id)), 2) as percentage,
                    RANK() OVER (PARTITION BY p.id ORDER BY vt.vote_count DESC) as ranking
                FROM positions p
                JOIN vote_tallies vt ON p.id = vt.position_id
                LEFT JOIN candidates cand ON vt.candidate_id = cand.id
                LEFT JOIN users c ON cand.user_id = c.id
                WHERE p.election_id = ?
                ORDER BY p.order_index, vt.vote_count DESC
            ", [$election->id]);
        });
    }

    public function getBatchedStatistics(Election $election): array
    {
        return Cache::remember("batch_stats_{$election->id}", 300, function() use ($election) {
            $stats = DB::select("
                SELECT 
                    COUNT(DISTINCT vt.user_id) as total_voters,
                    COUNT(v.id) as total_votes,
                    COUNT(DISTINCT p.id) as total_positions,
                    COUNT(DISTINCT c.id) as total_candidates,
                    AVG(vt.vote_count) as avg_votes_per_candidate,
                    MAX(vt.vote_count) as max_votes,
                    MIN(vt.vote_count) as min_votes
                FROM elections e
                LEFT JOIN positions p ON e.id = p.election_id
                LEFT JOIN candidates c ON p.id = c.position_id
                LEFT JOIN vote_tallies vt ON c.id = vt.candidate_id
                LEFT JOIN votes v ON e.id = v.election_id
                LEFT JOIN vote_tokens vt ON e.id = vt.election_id
                WHERE e.id = ?
                GROUP BY e.id
            ", [$election->id]);

            return $stats[0] ?? [];
        });
    }

    private function preloadElectionData(Election $election): void
    {
        // Eager load all related data in single queries
        $election->load([
            'positions.voteTallies.candidate.user',
            'votes' => fn($q) => $q->select(['id', 'election_id', 'cast_at', 'receipt_hash']),
            'voteTokens' => fn($q) => $q->select(['id', 'election_id', 'is_used', 'used_at'])
        ]);
    }

    private function optimizeResultsCache(Election $election): void
    {
        if ($election->isEnded()) {
            // Cache final results permanently for ended elections
            Cache::forever("final_results_{$election->id}", $this->getOptimizedResults($election));
        }
    }

    public function clearElectionCache(Election $election): void
    {
        $keys = [
            "optimized_results_{$election->id}",
            "batch_stats_{$election->id}",
            "final_results_{$election->id}",
            "election_integrity_{$election->id}",
            "vote_proofs_{$election->id}"
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}