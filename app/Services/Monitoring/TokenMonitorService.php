<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class TokenMonitorService
{
    public function getStats(?int $electionId = null): array
    {
        $cacheKey = 'token_stats_' . ($electionId ?? 'all');
        
        return Cache::remember($cacheKey, 60, function() use ($electionId) {
            $query = DB::table('vote_tokens');
            
            if ($electionId) {
                $query->where('election_id', $electionId);
            }

            $stats = $query->selectRaw('COUNT(*) as total, SUM(is_used) as used')->first();
            $total = $stats->total ?? 0;
            $used = $stats->used ?? 0;

            $byElection = DB::table('vote_tokens')
                ->join('elections', 'vote_tokens.election_id', '=', 'elections.id')
                ->selectRaw('elections.title, COUNT(*) as total, SUM(vote_tokens.is_used) as used')
                ->groupBy('elections.id', 'elections.title')
                ->get()
                ->map(fn($item) => [
                    'election' => $item->title,
                    'total' => $item->total,
                    'used' => $item->used,
                    'unused' => $item->total - $item->used
                ]);

            return [
                'total' => $total,
                'used' => $used,
                'unused' => $total - $used,
                'usage_rate' => $total > 0 ? round(($used / $total) * 100, 1) : 0,
                'by_election' => $byElection
            ];
        });
    }

    public function getTokens(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = DB::table('vote_tokens')
            ->join('users', 'vote_tokens.user_id', '=', 'users.id')
            ->join('elections', 'vote_tokens.election_id', '=', 'elections.id')
            ->leftJoin('vote_records', 'vote_tokens.vote_receipt_hash', '=', 'vote_records.receipt_hash')
            ->select(
                'vote_tokens.id',
                'vote_tokens.is_used',
                'vote_tokens.created_at',
                'users.first_name',
                'users.last_name', 
                'users.email',
                'elections.title as election_title',
                'vote_records.cast_at as used_at'
            );

        if (!empty($filters['election_id']) && $filters['election_id'] !== 'all') {
            $query->where('vote_tokens.election_id', $filters['election_id']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'used') {
                $query->where('vote_tokens.is_used', true);
            } elseif ($filters['status'] === 'unused') {
                $query->where('vote_tokens.is_used', false);
            }
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('users.first_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('users.last_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('users.email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('elections.title', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('vote_tokens.created_at', 'desc')->paginate($perPage);
    }

    public function getElections(): \Illuminate\Support\Collection
    {
        return Cache::remember('elections_list', 300, function() {
            return DB::table('elections')
                ->select('id', 'title')
                ->orderBy('title')
                ->get();
        });
    }

    public function clearCache(): void
    {
        Cache::forget('elections_list');
        Cache::flush(); // Clear all token stats cache
    }
}