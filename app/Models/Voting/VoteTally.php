<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Candidate\Candidate;

class VoteTally extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'election_id',
        'position_id',
        'candidate_id',
        'vote_count',
        'last_updated',
        'tally_hash',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'vote_count' => 'integer',
            'last_updated' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Scopes
     */
    public function scopeForElection(\Illuminate\Database\Eloquent\Builder $query, int $electionId)
    {
        return $query->where('election_id', '=', $electionId);
    }

    public function scopeForPosition(\Illuminate\Database\Eloquent\Builder $query, int $positionId)
    {
        return $query->where('position_id', '=', $positionId);
    }

    public function scopeForCandidate(\Illuminate\Database\Eloquent\Builder $query, int $candidateId)
    {
        return $query->where('candidate_id', '=', $candidateId);
    }

    public function scopeOrderedByVotes($query, string $direction = 'desc')
    {
        $allowedDirections = ['asc', 'desc'];
        $lowerDirection = strtolower($direction);
        $safeDirection = in_array($lowerDirection, $allowedDirections, true) ? $lowerDirection : 'desc';
        
        return $query->orderBy('vote_count', $safeDirection);
    }

    public function scopeWithVotes($query)
    {
        return $query->where('vote_count', '>', 0);
    }

    public function scopeAbstentions($query)
    {
        return $query->whereNull('candidate_id');
    }

    /**
     * Helper methods
     */
    public function isAbstention(): bool
    {
        return is_null($this->candidate_id);
    }

    public function hasVotes(): bool
    {
        return $this->vote_count > 0;
    }

    public function incrementVoteCount(): void
    {
        $currentTime = app(\App\Services\Election\ElectionTimeService::class)->getCurrentTime();
        $newCount = $this->vote_count + 1;
        
        $tallyData = [
            'election_id' => $this->election_id,
            'position_id' => $this->position_id,
            'candidate_id' => $this->candidate_id,
            'vote_count' => $newCount,
            'last_updated' => $currentTime->format('Y-m-d H:i:s'),
        ];

        $this->update([
            'vote_count' => $newCount,
            'tally_hash' => hash('sha256', json_encode($tallyData)),
            'last_updated' => $currentTime,
        ]);
    }

    public function decrementVoteCount(): void
    {
        if ($this->vote_count > 0) {
            $this->decrement('vote_count');
            $this->updateTallyHash();
        }
    }

    public function updateTallyHash(): void
    {
        $currentTime = app(\App\Services\Election\ElectionTimeService::class)->getCurrentTime();
        
        $tallyData = [
            'election_id' => $this->election_id,
            'position_id' => $this->position_id,
            'candidate_id' => $this->candidate_id,
            'vote_count' => $this->vote_count,
            'last_updated' => $currentTime->format('Y-m-d H:i:s'),
        ];

        $this->update([
            'tally_hash' => hash('sha256', json_encode($tallyData)),
            'last_updated' => $currentTime,
        ]);
    }

    public function getPercentage(): float
    {
        $totalVotes = $this->position->getTotalVotes();
        
        return $totalVotes > 0 ? 
            round(($this->vote_count / $totalVotes) * 100, 2) : 0;
    }

    public function getRanking(): int
    {
        return Cache::remember("tally_ranking_{$this->id}", 300, function() {
            return static::where('position_id', '=', $this->position_id)
                ->where('vote_count', '>', $this->vote_count)
                ->count() + 1;
        });
    }

    public function isWinning(): bool
    {
        $maxSelections = $this->position->max_selections;
        $ranking = $this->getRanking();
        
        return $ranking <= $maxSelections;
    }

    public function isLeading(): bool
    {
        return $this->getRanking() === 1;
    }

    public function getCandidateName(): string
    {
        if ($this->isAbstention()) {
            return 'Abstention';
        }

        return $this->candidate?->user?->full_name ?? 'Unknown Candidate';
    }

    public function getDisplayData(): array
    {
        $ranking = $this->getRanking();
        
        return [
            'id' => $this->id,
            'candidate_id' => $this->candidate_id,
            'candidate_name' => $this->getCandidateName(),
            'vote_count' => $this->vote_count,
            'percentage' => $this->getPercentage(),
            'ranking' => $ranking,
            'is_winning' => $ranking <= $this->position->max_selections,
            'is_leading' => $ranking === 1,
            'is_abstention' => $this->isAbstention(),
            'last_updated' => $this->last_updated,
        ];
    }

    public function verifyIntegrity(): bool
    {
        $tallyData = [
            'election_id' => $this->election_id,
            'position_id' => $this->position_id,
            'candidate_id' => $this->candidate_id,
            'vote_count' => $this->vote_count,
            'last_updated' => $this->last_updated?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
        ];

        $expectedHash = hash('sha256', json_encode($tallyData));
        
        return $this->tally_hash === $expectedHash;
    }

    /**
     * Caching methods
     */
    public static function getCachedElectionResults(int $electionId, int $cacheDuration = 300): array
    {
        $cacheKey = "election_results_" . $electionId;

        return Cache::remember($cacheKey, $cacheDuration, function () use ($electionId) {
            return static::getElectionSummary($electionId);
        });
    }

    public static function getCachedPositionResults(int $positionId, int $cacheDuration = 300): array
    {
        $cacheKey = "position_results_" . $positionId;

        return Cache::remember($cacheKey, $cacheDuration, function () use ($positionId) {
            return static::getPositionResults($positionId);
        });
    }

    public static function clearElectionCache(int $electionId): void
    {
        Cache::forget("election_results_{$electionId}");

        try {
            $positions = Position::where('election_id', $electionId)->pluck('id');
            foreach ($positions as $positionId) {
                Cache::forget("position_results_{$positionId}");
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear position caches', ['election_id' => $electionId]);
        }
    }

    public static function clearPositionCache(int $positionId): void
    {
        Cache::forget("position_results_{$positionId}");

        try {
            $position = Position::find($positionId);
            if ($position) {
                Cache::forget("election_results_{$position->election_id}");
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear election cache', ['position_id' => $positionId]);
        }
    }

    /**
     * Static methods
     */
    public static function createOrUpdateTally(int $electionId, int $positionId, ?int $candidateId = null): self
    {
        $currentTime = app(\App\Services\Election\ElectionTimeService::class)->getCurrentTime();
        
        $tally = static::firstOrCreate([
            'election_id' => $electionId,
            'position_id' => $positionId,
            'candidate_id' => $candidateId,
        ], [
            'vote_count' => 0,
            'tally_hash' => hash('sha256', json_encode([
                'election_id' => $electionId,
                'position_id' => $positionId,
                'candidate_id' => $candidateId,
                'vote_count' => 0,
                'last_updated' => $currentTime->format('Y-m-d H:i:s'),
            ])),
            'last_updated' => $currentTime,
        ]);

        $tally->incrementVoteCount();
        
        return $tally;
    }

    public static function getPositionResults(int $positionId): array
    {
        $tallies = static::where('position_id', '=', $positionId)
            ->with(['candidate.user'])
            ->orderBy('vote_count', 'desc')
            ->get();

        $totalVotes = $tallies->sum('vote_count');

        return $tallies->map(function ($tally, $index) use ($totalVotes) {
            $ranking = $index + 1;
            return [
                'candidate_id' => $tally->candidate_id,
                'candidate_name' => $tally->getCandidateName(),
                'vote_count' => $tally->vote_count,
                'percentage' => $totalVotes > 0 ? 
                    round(($tally->vote_count / $totalVotes) * 100, 2) : 0,
                'ranking' => $ranking,
                'is_winning' => $ranking <= ($tally->position->max_selections ?? 1),
            ];
        })->toArray();
    }

    public static function getElectionSummary(int $electionId): array
    {
        $tallies = static::where('election_id', '=', $electionId)
            ->with(['position', 'candidate.user'])
            ->get()
            ->groupBy('position_id');

        $summary = [];

        foreach ($tallies as $positionId => $positionTallies) {
            $safePositionId = abs((int)$positionId);
            $firstTally = $positionTallies->first();
            if (!$firstTally) continue;
            $position = $firstTally->position;
            $totalVotes = $positionTallies->sum('vote_count');

            $summary[$safePositionId] = [
                'position_title' => $position?->title ?? 'Unknown Position',
                'total_votes' => $totalVotes,
                'results' => $positionTallies->sortByDesc('vote_count')->map(function ($tally) use ($totalVotes) {
                    return [
                        'candidate_name' => $tally->getCandidateName(),
                        'vote_count' => $tally->vote_count,
                        'percentage' => $totalVotes > 0 ? 
                            round(($tally->vote_count / $totalVotes) * 100, 2) : 0,
                    ];
                })->values()->toArray(),
            ];
        }

        return $summary;
    }
}