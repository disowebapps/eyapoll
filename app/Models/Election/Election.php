<?php

namespace App\Models\Election;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\User;
use App\Models\Candidate\Candidate;
use App\Models\Voting\VoteRecord;
use App\Models\Voting\VoteToken;
use App\Models\Voting\VoteTally;
use App\Enums\Election\ElectionType;
use App\Enums\Election\ElectionStatus;

class Election extends Model
{
    use HasFactory, SoftDeletes;
    
    public ?int $id = null;
    public ?string $uuid = null;
    public ?string $title = null;
    public ?string $certification_hash = null;
    
    private static $timeService;

    /**
     * The attributes that are guarded from mass assignment.
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'chain_hash',
        'certification_hash',
        'finalization_hash',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'type' => ElectionType::class,
            'status' => ElectionStatus::class,
            'phase' => \App\Enums\Election\ElectionPhase::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'settings' => 'json',
            'results_published' => 'boolean',
            'voter_register_locked' => 'boolean',
            'voting_closed' => 'boolean',
            'voter_register_starts' => 'datetime',
            'voter_register_ends' => 'datetime',
            'voter_register_published' => 'datetime',
            'candidate_register_starts' => 'datetime',
            'candidate_register_ends' => 'datetime',
            'candidate_list_published' => 'datetime',
            'registration_resumed_at' => 'datetime',
            'certified_at' => 'datetime',
            'finalized_at' => 'datetime',
            'certification_data' => 'json',
            'finalization_data' => 'json',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($election) {
            if (empty($election->uuid)) {
                $election->uuid = \Illuminate\Support\Str::uuid();
            }
        });

        // Vote tokens are generated during VERIFICATION phase transition
        // via ElectionPhaseManager for proper civic process control
    }

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class)->orderBy('order_index');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function approvedCandidates(): HasMany
    {
        $approvedStatus = defined('\App\Enums\Candidate\CandidateStatus::APPROVED') 
            ? \App\Enums\Candidate\CandidateStatus::APPROVED->value 
            : 'approved';
        return $this->hasMany(Candidate::class)->where('status', $approvedStatus);
    }

    public function voteRecords(): HasMany
    {
        return $this->hasMany(VoteRecord::class);
    }

    public function voteTokens(): HasMany
    {
        return $this->hasMany(VoteToken::class);
    }

    public function voteTallies(): HasMany
    {
        return $this->hasMany(VoteTally::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(\App\Models\Election\ElectionSnapshot::class);
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(\App\Models\ElectionAppeal::class);
    }

    public function certifier(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'certified_by');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'finalized_by');
    }

    /**
     * Scopes
     */
    public function scopeUpcomingStatus($query)
    {
        return $query->where('status', ElectionStatus::UPCOMING->value);
    }

    public function scopeOngoingStatus($query)
    {
        return $query->where('status', ElectionStatus::ONGOING->value);
    }

    public function scopeCompletedStatus($query)
    {
        return $query->where('status', ElectionStatus::COMPLETED->value);
    }

    public function scopeCertifiedStatus($query)
    {
        return $query->where('status', ElectionStatus::CERTIFIED->value);
    }

    public function scopeFinalizedStatus($query)
    {
        return $query->where('status', ElectionStatus::FINALIZED->value);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', ElectionStatus::ARCHIVED->value);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', ElectionStatus::CANCELLED->value);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('starts_at', '<=', now())
                    ->where('ends_at', '>', now())
                    ->where('status', ElectionStatus::ONGOING->value);
    }

    public function scopeByType($query, ElectionType $type)
    {
        return $query->where('type', $type->value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', ElectionStatus::ONGOING->value);
    }

    public function scopeEnded($query)
    {
        return $query->whereIn('status', [
            ElectionStatus::COMPLETED->value,
            ElectionStatus::CERTIFIED->value,
            ElectionStatus::FINALIZED->value
        ]);
    }

    /**
     * Helper methods
     */
    public function isScheduled(): bool
    {
        return $this->status === ElectionStatus::UPCOMING;
    }

    public function isActive(): bool
    {
        try {
            return $this->getTimeService()->getElectionStatus($this) === ElectionStatus::ONGOING;
        } catch (\App\Exceptions\InvalidElectionDatesException $e) {
            return false;
        }
    }

    public function isEnded(): bool
    {
        try {
            return $this->getTimeService()->getElectionStatus($this) === ElectionStatus::COMPLETED;
        } catch (\App\Exceptions\InvalidElectionDatesException $e) {
            return false;
        }
    }
    
    public function isArchived(): bool
    {
        return $this->status === ElectionStatus::ARCHIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === ElectionStatus::CANCELLED;
    }

    public function canBeEdited(): bool
    {
        return $this->status->canBeEdited();
    }

    public function canBeStarted(): bool
    {
        return $this->status->canBeStarted() && $this->starts_at && $this->starts_at <= now();
    }

    public function canBeEnded(): bool
    {
        return $this->status->canBeEnded();
    }

    public function canBeCancelled(): bool
    {
        return $this->status->canBeCancelled();
    }

    public function canAcceptVotes(): bool
    {
        return $this->getTimeService()->canAcceptVotes($this);
    }
    
    private function getTimeService()
    {
        if (!self::$timeService) {
            self::$timeService = app(\App\Services\Election\ElectionTimeService::class);
        }
        return self::$timeService;
    }

    public function isAcceptingVotes(): bool
    {
        return $this->canAcceptVotes();
    }

    public function canAcceptCandidateApplications(): bool
    {
        return $this->status->canAcceptCandidateApplications();
    }



    public function hasResults(): bool
    {
        return $this->status->hasResults();
    }

    public function allowsResultsViewing(): bool
    {
        return $this->status->allowsResultsViewing();
    }

    public function canPublishResults(): bool
    {
        return $this->isEnded();
    }

    public function canBeCertified(): bool
    {
        return $this->status->canBeCertified();
    }

    public function canBeFinalized(): bool
    {
        return $this->status->canBeFinalized();
    }

    public function canBeArchived(): bool
    {
        return $this->status->canBeArchived();
    }

    public function requiresConsensusToStart(): bool
    {
        return $this->type->requiresConsensusToStart();
    }

    public function requiresConsensusToEnd(): bool
    {
        return $this->status->requiresConsensusToChange();
    }

    public function getTimeRemaining(): ?string
    {
        if (!$this->isActive() || !$this->ends_at || $this->ends_at <= now()) {
            return null;
        }

        $diff = now()->diff($this->ends_at);
        
        if ($diff->days > 0) {
            return $diff->days . ' days, ' . $diff->h . ' hours';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hours, ' . $diff->i . ' minutes';
        } else {
            return $diff->i . ' minutes';
        }
    }

    public function getVoterTurnout(): array
    {
        $stats = $this->voteTokens()
            ->selectRaw('COUNT(*) as total_eligible, SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) as total_voted')
            ->first();
        
        $totalEligible = $stats->total_eligible ?? 0;
        $totalVoted = $stats->total_voted ?? 0;
        $percentage = $totalEligible > 0 ? round(($totalVoted / $totalEligible) * 100, 2) : 0;

        return [
            'total_eligible' => $totalEligible,
            'total_voted' => $totalVoted,
            'percentage' => $percentage,
        ];
    }

    public function getTotalVoteRecords(): int
    {
        return $this->voteRecords()->count();
    }

    public function hasVotes(): bool
    {
        return $this->voteRecords()->exists();
    }

    public function getStatusColor(): string
    {
        return $this->status?->color() ?? 'gray';
    }

    public function getTypeColor(): string
    {
        return $this->type?->color() ?? 'gray';
    }

    public function getTypeIcon(): string
    {
        return $this->type?->icon() ?? 'heroicon-o-question-mark-circle';
    }

    public function getDurationInHours(): int
    {
        if (!$this->starts_at || !$this->ends_at) {
            return 0;
        }
        return $this->starts_at->diffInRealHours($this->ends_at);
    }

    public function isEndingSoon(int $hoursThreshold = 2): bool
    {
        if (!$this->isActive() || !$this->ends_at) {
            return false;
        }

        return $this->ends_at->diffInRealHours(now()) <= $hoursThreshold;
    }

    public function hasStarted(): bool
    {
        return $this->starts_at && $this->starts_at <= now();
    }

    public function willStartSoon(int $hoursThreshold = 24): bool
    {
        if ($this->hasStarted() || !$this->starts_at) {
            return false;
        }

        return $this->starts_at->diffInRealHours(now()) <= $hoursThreshold;
    }

    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    public function getStatusBadgeClass(): string
    {
        return $this->status?->getBadgeClass() ?? 'bg-gray-100 text-gray-800';
    }

    public function canIssueTokens(): bool
    {
        return $this->status === ElectionStatus::UPCOMING && 
               !$this->voter_register_locked &&
               $this->starts_at > now();
    }

    public function hasEnded(): bool
    {
        return $this->ends_at && $this->ends_at <= now();
    }

    // REMOVED: generateVoteTokens() method
    // Vote tokens are SACRED and can ONLY be generated through:
    // 1. Manual accreditation process (AccreditationService)
    // 2. Voter register publication (VoterRegistrationService)
    // Any other token generation is STRICTLY FORBIDDEN
}