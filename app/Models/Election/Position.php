<?php

namespace App\Models\Election;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Candidate\Candidate;
use App\Models\Voting\VoteRecord;
use App\Models\Voting\VoteTally;

class Position extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'election_id',
        'title',
        'description',
        'max_selections',
        'order_index',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'max_selections' => 'integer',
            'order_index' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->orderBy('created_at');
    }

    public function approvedCandidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->where('status', 'approved');
    }

    public function voteRecords(): HasMany
    {
        return $this->hasMany(VoteRecord::class);
    }

    public function voteTallies(): HasMany
    {
        return $this->hasMany(VoteTally::class)->orderBy('vote_count', 'desc');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    /**
     * Helper methods
     */
    public function allowsMultipleSelections(): bool
    {
        return $this->max_selections > 1;
    }

    public function isSingleChoice(): bool
    {
        return $this->max_selections === 1;
    }

    public function getTotalVotes(): int
    {
        return $this->voteRecords()->count();
    }

    public function getTotalCandidates(): int
    {
        return $this->candidates()->count();
    }

    public function getApprovedCandidatesCount(): int
    {
        return $this->approvedCandidates()->count();
    }

    public function hasEnoughCandidates(): bool
    {
        // For single choice positions, need at least 2 candidates
        // For multiple choice, need at least max_selections + 1
        $minRequired = $this->isSingleChoice() ? 2 : $this->max_selections + 1;
        
        return $this->getApprovedCandidatesCount() >= $minRequired;
    }

    public function canAcceptMoreCandidates(): bool
    {
        return $this->election->canAcceptCandidateApplications() && $this->is_active;
    }

    public function getWinners(?Election $election = null): array
    {
        $electionToCheck = $election ?? $this->election;
        
        if (!$electionToCheck || !$electionToCheck->hasResults()) {
            return [];
        }

        $tallies = $this->voteTallies()
            ->with('candidate.user')
            ->where('vote_count', '>', 0)
            ->orderBy('vote_count', 'desc')
            ->take($this->max_selections)
            ->get();

        return $tallies->map(function ($tally) {
            return [
                'candidate' => $tally->candidate,
                'votes' => $tally->vote_count,
                'percentage' => $this->getTotalVotes() > 0 ? 
                    round(($tally->vote_count / $this->getTotalVotes()) * 100, 2) : 0,
            ];
        })->toArray();
    }

    public function getResultsSummary(): array
    {
        $totalVotes = $this->getTotalVotes();
        $tallies = $this->voteTallies()
            ->with('candidate.user')
            ->orderBy('vote_count', 'desc')
            ->get();

        return [
            'total_votes' => $totalVotes,
            'total_candidates' => $this->getTotalCandidates(),
            'results' => $tallies->map(function ($tally) use ($totalVotes) {
                return [
                    'candidate_id' => $tally->candidate_id,
                    'candidate_name' => $tally->candidate?->user->full_name ?? 'Abstention',
                    'votes' => $tally->vote_count,
                    'percentage' => $totalVotes > 0 ? 
                        round(($tally->vote_count / $totalVotes) * 100, 2) : 0,
                ];
            })->toArray(),
        ];
    }

    public function getSelectionInstructions(): string
    {
        if ($this->isSingleChoice()) {
            return 'Select one candidate';
        } else {
            return "Select up to {$this->max_selections} candidates";
        }
    }

    public function validateBallotSelections(array $selections): bool
    {
        // Check if number of selections is within limits
        if (count($selections) > $this->max_selections) {
            return false;
        }

        // Check if all selected candidates are approved for this position
        $approvedCandidateIds = $this->approvedCandidates()->pluck('id')->toArray();
        
        foreach ($selections as $candidateId) {
            if (!in_array((int) $candidateId, $approvedCandidateIds)) {
                return false;
            }
        }

        return true;
    }
}