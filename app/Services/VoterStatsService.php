<?php

namespace App\Services;

use App\Models\User;
use App\Models\Election\Election;
use App\Models\Voting\VoteRecord;
use App\Services\Voting\VoterHashService;
use App\Exceptions\InvalidElectionDatesException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class VoterStatsService
{
    public function __construct(
        private VoterHashService $voterHashService
    ) {}

    public function getElectionStats(User $user): array
    {
        try {
            // Get all elections with full data needed for time service
            $allElections = Election::select('id', 'starts_at', 'ends_at', 'status')->get();
            $timeService = app(\App\Services\Election\ElectionTimeService::class);

            $activeElections = $allElections->filter(function($election) use ($timeService) {
                try {
                    return $timeService->getElectionStatus($election) === \App\Enums\Election\ElectionStatus::ONGOING;
                } catch (InvalidElectionDatesException $e) {
                    Log::warning('Skipping invalid election in dashboard data', [
                        'election_id' => $election->id,
                        'error' => $e->getMessage()
                    ]);
                    return false;
                }
            });

            $totalActive = $activeElections->count();

            if ($totalActive === 0) {
                return [
                    'total' => 0,
                    'voted' => 0,
                    'status' => 'none',
                    'text' => 'No active elections'
                ];
            }

            $votedCount = $this->getVotedElectionsCount($user, $activeElections);

            return [
                'total' => $totalActive,
                'voted' => $votedCount,
                'status' => $this->determineStatus($totalActive, $votedCount),
                'text' => $this->generateText($totalActive, $votedCount)
            ];

        } catch (Exception $e) {
            Log::error('Failed to get election stats', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'total' => 0,
                'voted' => 0,
                'status' => 'error',
                'text' => 'Unable to load election data'
            ];
        }
    }

    private function getVotedElectionsCount(User $user, $activeElections): int
    {
        try {
            // Batch generate voter hashes for performance
            $voterHashes = [];
            foreach ($activeElections as $election) {
                $voterHashes[$election->id] = $this->voterHashService->generateVoterHash($user, $election);
            }
            
            // Single query to check all elections
            $votedElectionIds = VoteRecord::whereIn('voter_hash', $voterHashes)
                ->whereIn('election_id', $activeElections->pluck('id'))
                ->distinct('election_id')
                ->pluck('election_id')
                ->toArray();
                
            return count($votedElectionIds);
            
        } catch (Exception $e) {
            Log::error('Failed to get voted elections count', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    private function determineStatus(int $total, int $voted): string
    {
        if ($total === 1) {
            return $voted === 1 ? 'completed' : 'ready';
        }
        
        if ($voted === $total) {
            return 'completed';
        }
        
        return $voted > 0 ? 'partial' : 'ready';
    }

    private function generateText(int $total, int $voted): string
    {
        if ($total === 1) {
            return $voted === 1 ? 'Already voted' : 'Ready to vote';
        }
        
        if ($voted === $total) {
            return 'Voted in all elections';
        }
        
        $remaining = $total - $voted;
        if ($voted === 0) {
            return "Vote <span class='text-blue-600 font-bold'>{$total}</span> elections";
        }
        
        return "Vote <span class='text-blue-600 font-bold'>{$remaining}</span> of <span class='text-blue-600 font-bold'>{$total}</span> elections";
    }

    public function getDashboardData(User $user, int $perPage = 10): array
    {
        return Cache::remember("user_dashboard_{$user->id}_{$perPage}", 180, function() use ($user, $perPage) {
            try {
                // Get all elections and filter for recent elections (not archived or cancelled)
                $allElections = Election::select('id', 'title', 'starts_at', 'ends_at', 'status', 'description', 'type', 'candidate_register_starts', 'candidate_register_ends')
                    ->whereNotIn('status', [\App\Enums\Election\ElectionStatus::ARCHIVED->value, \App\Enums\Election\ElectionStatus::CANCELLED->value])
                    ->orderBy('ends_at', 'desc')
                    ->get();

                $timeService = app(\App\Services\Election\ElectionTimeService::class);

                // Get time-based statuses for all elections
                $recentElections = $allElections->filter(function($election) use ($timeService) {
                    try {
                        $status = $timeService->getElectionStatus($election);
                        // Include upcoming, ongoing, and recently completed elections
                        return in_array($status, [
                            \App\Enums\Election\ElectionStatus::UPCOMING,
                            \App\Enums\Election\ElectionStatus::ONGOING,
                            \App\Enums\Election\ElectionStatus::COMPLETED
                        ]);
                    } catch (InvalidElectionDatesException $e) {
                        Log::warning('Skipping invalid election in dashboard data', [
                            'election_id' => $election->id,
                            'error' => $e->getMessage()
                        ]);
                        return false;
                    }
                });

                $votedElectionIds = $this->getVotedElectionIds($user, $recentElections);
                $voteRecords = $this->getVoteRecords($user);
                $eligibleElectionsCount = $user->voteTokens()->distinct('election_id')->count();

                // Get active elections count for stats
                $activeElections = $recentElections->filter(function($election) use ($timeService) {
                    try {
                        return $timeService->getElectionStatus($election) === \App\Enums\Election\ElectionStatus::ONGOING;
                    } catch (InvalidElectionDatesException $e) {
                        return false;
                    }
                });

                // Paginate elections
                $paginatedElections = $recentElections->forPage(1, $perPage);

                // Add application status for each election
                foreach ($paginatedElections as $election) {
                    $hasApplied = \App\Models\Candidate\Candidate::where('user_id', $user->id)
                        ->where('election_id', $election->id)
                        ->whereIn('status', ['pending', 'approved'])
                        ->exists();
                    $election->user_has_applied = $hasApplied;
                    $election->application_ended = $election->candidate_register_ends && now()->gt($election->candidate_register_ends);
                }

                return [
                    'elections' => $paginatedElections,
                    'voted_ids' => $votedElectionIds,
                    'vote_records' => $voteRecords->take(5),
                    'votes_cast' => $voteRecords->count(),
                    'active_count' => $activeElections->count(),
                    'participation_rate' => $eligibleElectionsCount > 0 ? round(($voteRecords->count() / $eligibleElectionsCount) * 100) : 100,
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'has_more' => $recentElections->count() > $perPage
                ];

            } catch (Exception $e) {
                Log::error('Failed to get dashboard data', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);

                return [
                    'elections' => collect(),
                    'voted_ids' => [],
                    'vote_records' => collect(),
                    'votes_cast' => 0,
                    'active_count' => 0,
                    'participation_rate' => 0,
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'has_more' => false
                ];
            }
        });
    }

    private function getVotedElectionIds(User $user, $activeElections): array
    {
        try {
            // Batch generate voter hashes
            $voterHashes = [];
            foreach ($activeElections as $election) {
                $voterHashes[$election->id] = $this->voterHashService->generateVoterHash($user, $election);
            }
            
            // Single query for all elections
            return VoteRecord::whereIn('voter_hash', $voterHashes)
                ->whereIn('election_id', $activeElections->pluck('id'))
                ->distinct('election_id')
                ->pluck('election_id')
                ->toArray();
                
        } catch (Exception $e) {
            Log::error('Failed to get voted election IDs', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function getVoteRecords(User $user)
    {
        return Cache::remember("user_vote_records_{$user->id}", 300, function() use ($user) {
            try {
                $allElections = Election::select('id', 'title', 'ends_at')->get();
                
                // Batch generate voter hashes
                $voterHashes = [];
                foreach ($allElections as $election) {
                    $voterHashes[] = $this->voterHashService->generateVoterHash($user, $election);
                }
                
                // Single query with eager loading
                return VoteRecord::whereIn('voter_hash', $voterHashes)
                    ->with('election:id,title,ends_at')
                    ->orderBy('cast_at', 'desc')
                    ->get();
                    
            } catch (Exception $e) {
                Log::error('Failed to get vote records', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                return collect();
            }
        });
    }
    public function getRecentVoteHistory(User $user, int $limit = 4)
    {
        return Cache::remember("user_recent_vote_history_{$user->id}_{$limit}", 300, function() use ($user, $limit) {
            try {
                $allElections = Election::select('id', 'title', 'ends_at')->get();

                // Batch generate voter hashes
                $voterHashes = [];
                foreach ($allElections as $election) {
                    $voterHashes[] = $this->voterHashService->generateVoterHash($user, $election);
                }

                // Get recent vote records with election data
                $voteRecords = VoteRecord::whereIn('voter_hash', $voterHashes)
                    ->with('election:id,title,ends_at')
                    ->orderBy('cast_at', 'desc')
                    ->take($limit)
                    ->get();

                // Process each vote record to include candidate information
                $history = [];
                foreach ($voteRecords as $record) {
                    try {
                        $selections = decrypt($record->encrypted_selections);
                        $candidates = $this->getCandidateNamesFromSelections($selections);

                        $history[] = [
                            'election_title' => $record->election->title,
                            'cast_at' => $record->cast_at,
                            'candidates' => $candidates,
                            'receipt_hash' => $record->receipt_hash
                        ];
                    } catch (Exception $e) {
                        Log::warning('Failed to decrypt vote selections', [
                            'vote_record_id' => $record->id,
                            'error' => $e->getMessage()
                        ]);
                        // Include record without candidate info if decryption fails
                        $history[] = [
                            'election_title' => $record->election->title,
                            'cast_at' => $record->cast_at,
                            'candidates' => 'Unable to retrieve',
                            'receipt_hash' => $record->receipt_hash
                        ];
                    }
                }

                return $history;

            } catch (Exception $e) {
                Log::error('Failed to get recent vote history', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    private function getCandidateNamesFromSelections(array $selections): string
    {
        $candidateNames = [];

        foreach ($selections as $positionId => $candidateIds) {
            if (empty($candidateIds)) {
                $candidateNames[] = 'Abstained';
            } else {
                foreach ($candidateIds as $candidateId) {
                    $candidate = \App\Models\Candidate\Candidate::find($candidateId);
                    if ($candidate) {
                        $candidateNames[] = $candidate->name;
                    } else {
                        $candidateNames[] = 'Unknown Candidate';
                    }
                }
            }
        }

        return implode(', ', $candidateNames);
    }
    public function getElectionsData(User $user, int $perPage = 10, string $statusFilter = 'all'): array
    {
        // Don't cache for now to ensure fresh data
        try {
            $query = Election::select('id', 'title', 'starts_at', 'ends_at', 'status', 'description', 'type', 'candidate_register_starts', 'candidate_register_ends')
                ->orderBy('ends_at', 'desc');

            if ($statusFilter !== 'all') {
                if ($statusFilter === 'upcoming' || $statusFilter === 'ongoing' || $statusFilter === 'completed') {
                    $timeService = app(\App\Services\Election\ElectionTimeService::class);
                    $query->where(function($q) use ($timeService, $statusFilter) {
                        $allElections = $q->get();
                        $filteredIds = [];
                        foreach ($allElections as $election) {
                            try {
                                $currentStatus = $timeService->getElectionStatus($election);
                                if ($currentStatus->value === $statusFilter) {
                                    $filteredIds[] = $election->id;
                                }
                            } catch (\Exception $e) {}
                        }
                        $q->whereIn('id', $filteredIds);
                    });
                } else {
                    $query->where('status', $statusFilter);
                }
            } else {
                $query->whereNotIn('status', [\App\Enums\Election\ElectionStatus::ARCHIVED->value, \App\Enums\Election\ElectionStatus::CANCELLED->value]);
            }

            $elections = $query->get();
            
            // Add application status for each election
            foreach ($elections as $election) {
                $hasApplied = \App\Models\Candidate\Candidate::where('user_id', $user->id)
                    ->where('election_id', $election->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();
                $election->user_has_applied = $hasApplied;
                $election->application_ended = $election->candidate_register_ends && now()->gt($election->candidate_register_ends);
                $election->user_can_apply = $user->role->canApplyAsCandidate() && $user->isApproved();
            }

            $votedElectionIds = $this->getVotedElectionIds($user, $elections);
            $paginatedElections = $elections->forPage(1, $perPage);

            return [
                'elections' => $paginatedElections,
                'voted_ids' => $votedElectionIds,
                'current_page' => 1,
                'per_page' => $perPage,
                'has_more' => $elections->count() > $perPage,
                'total' => $elections->count()
            ];

        } catch (Exception $e) {
            Log::error('Failed to get elections data', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'elections' => collect(),
                'voted_ids' => [],
                'current_page' => 1,
                'per_page' => $perPage,
                'has_more' => false,
                'total' => 0
            ];
        }
    }
}