<?php

namespace App\Models\Candidate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use App\Models\Admin;
use App\Models\User;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Voting\VoteTally;
use App\Models\Candidate\CandidateActionHistory;
use App\Models\Candidate\PaymentHistory;
use App\Models\Candidate\PaymentProof;
use App\Enums\Candidate\CandidateStatus;
use App\Enums\Candidate\PaymentStatus;

/**
 * @property int $id
 * @property string $user_id
 * @property string $election_id
 * @property string $position_id
 * @property string $manifesto
 * @property float $application_fee
 * @property CandidateStatus $status
 * @property PaymentStatus $payment_status
 * @property string|null $approved_by
 * @property string|null $suspended_by
 * @property \Carbon\Carbon|null $approved_at
 * @property \Carbon\Carbon|null $suspended_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read User $user
 * @property-read Election $election
 * @property-read Position $position
 * @property-read Admin|null $approver
 * @property-read Admin|null $suspender
 * @property-read \Illuminate\Database\Eloquent\Collection|CandidateActionHistory[] $actionHistory
 * @property-read \Illuminate\Database\Eloquent\Collection|VoteTally[] $voteTallies
 * @property-read \Illuminate\Database\Eloquent\Collection|PaymentProof[] $paymentProofs
 */
class Candidate extends Authenticatable
{
    use HasFactory, Notifiable;

    public int $id;
    public ?string $suspension_reason;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id', 'election_id', 'position_id', 'manifesto', 'application_fee',
        'payment_status', 'payment_reference', 'status', 'rejection_reason',
        'approved_by', 'approved_at', 'suspended_by', 'suspended_at', 'suspension_reason',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected $hidden = [];

    protected function casts(): array
    {
        return [
            'status' => CandidateStatus::class,
            'payment_status' => PaymentStatus::class,
            'application_fee' => 'decimal:2',
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function suspender(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'suspended_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CandidateDocument::class);
    }

    public function voteTallies(): HasMany
    {
        return $this->hasMany(VoteTally::class);
    }

    public function actionHistory(): HasMany
    {
        return $this->hasMany(CandidateActionHistory::class)->orderBy('created_at', 'desc');
    }

    public function paymentHistory(): HasMany
    {
        return $this->hasMany(PaymentHistory::class)->orderBy('created_at', 'desc');
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class)->orderBy('created_at', 'desc');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', CandidateStatus::PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', CandidateStatus::APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', CandidateStatus::REJECTED);
    }

    public function scopeWithdrawn($query)
    {
        return $query->where('status', CandidateStatus::WITHDRAWN);
    }

    public function scopeEligible($query)
    {
        return $query->where('status', CandidateStatus::APPROVED);
    }

    public function scopeForElection($query, $electionId)
    {
        return $query->where('election_id', $electionId);
    }

    public function scopeForPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    public function scopePaymentCompleted($query)
    {
        return $query->whereIn('payment_status', [PaymentStatus::PAID, PaymentStatus::WAIVED]);
    }

    /**
     * Helper methods
     */
    public function isPending(): bool
    {
        return $this->status === CandidateStatus::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === CandidateStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === CandidateStatus::REJECTED;
    }

    public function isWithdrawn(): bool
    {
        return $this->status === CandidateStatus::WITHDRAWN;
    }

    public function isEligible(): bool
    {
        return $this->status->isEligibleForElection();
    }

    public function canWithdraw(): bool
    {
        return $this->status->canWithdraw() && 
               $this->election && 
               $this->election->canAcceptCandidateApplications();
    }

    public function canBeApproved(): bool
    {
        return $this->status->canBeApproved() && $this->hasCompletedPayment();
    }

    public function canBeRejected(): bool
    {
        return $this->status->canBeRejected();
    }

    public function canEditApplication(): bool
    {
        return $this->status->canEditApplication() && 
               $this->election && 
               $this->election->canAcceptCandidateApplications();
    }

    public function canUploadDocuments(): bool
    {
        return $this->status->canUploadDocuments() && 
               $this->election && 
               $this->election->canAcceptCandidateApplications();
    }

    public function hasCompletedPayment(): bool
    {
        return $this->payment_status->isCompleted();
    }

    public function requiresPayment(): bool
    {
        return $this->payment_status->requiresPayment() && $this->application_fee > 0;
    }

    public function getStatusColor(): string
    {
        return $this->status->color();
    }

    public function getPaymentStatusColor(): string
    {
        return $this->payment_status->color();
    }

    public function getVoteCount(): int
    {
        $tally = $this->voteTallies()->first();
        return $tally ? $tally->vote_count : 0;
    }

    public function getVotePercentage(): float
    {
        if (!$this->position) {
            return 0;
        }
        
        $totalVotes = $this->position->getTotalVotes();
        $myVotes = $this->getVoteCount();
        
        return $totalVotes > 0 ? round(($myVotes / $totalVotes) * 100, 2) : 0;
    }

    public function getRanking(): int
    {
        if (!$this->position) {
            return 1;
        }
        
        $myVotes = $this->getVoteCount();
        
        $higherRanked = $this->position->voteTallies()
            ->where('vote_count', '>', $myVotes)
            ->count();
            
        return $higherRanked + 1;
    }

    public function isWinner(): bool
    {
        if (!$this->position || !$this->election || !$this->election->hasResults()) {
            return false;
        }
        
        $winners = $this->position->getWinners($this->election);
        $winnerIds = collect($winners)->pluck('candidate.id')->toArray();
        
        return in_array($this->id, $winnerIds);
    }

    public function hasUploadedRequiredDocuments(): bool
    {
        $requiredTypes = ['cv']; // Basic requirement
        
        foreach ($requiredTypes as $type) {
            if (!$this->documents()->where('document_type', $type)->where('status', 'approved')->exists()) {
                return false;
            }
        }
        
        return true;
    }

    public function getApplicationProgress(): array
    {
        $steps = [
            'basic_info' => !empty($this->manifesto),
            'payment' => $this->hasCompletedPayment(),
            'documents' => $this->hasUploadedRequiredDocuments(),
            'approval' => $this->isApproved(),
        ];

        $completed = count(array_filter($steps));
        $total = count($steps);
        $percentage = round(($completed / $total) * 100);

        return [
            'steps' => $steps,
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage,
        ];
    }

    public function getDisplayName(): string
    {
        return $this->user ? $this->user->getFullNameAttribute() : 'Unknown';
    }

    public function getFullNameAttribute(): string
    {
        return $this->user ? $this->user->getFullNameAttribute() : 'Unknown';
    }

    public function getShortManifesto(int $length = 150): string
    {
        if (empty($this->manifesto)) {
            return 'No manifesto provided';
        }

        return \Illuminate\Support\Str::limit($this->manifesto, $length);
    }
}