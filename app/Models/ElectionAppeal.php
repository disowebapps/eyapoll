<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\Appeal\AppealStatus;
use App\Enums\Appeal\AppealType;
use App\Enums\Appeal\AppealPriority;
use App\Models\Election\Election;
use App\Models\Admin;

class ElectionAppeal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'election_id',
        'appellant_id',
        'type',
        'status',
        'priority',
        'title',
        'description',
        'appeal_data',
        'integrity_hash',
        'previous_hash',
        'submitted_at',
        'deadline_at',
        'assigned_to',
        'assigned_at',
        'reviewed_at',
        'review_notes',
        'resolution',
        'resolved_at',
        'resolved_by',
        'escalation_history',
    ];

    protected $casts = [
        'type' => AppealType::class,
        'status' => AppealStatus::class,
        'priority' => AppealPriority::class,
        'appeal_data' => 'json',
        'submitted_at' => 'datetime',
        'deadline_at' => 'datetime',
        'assigned_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'escalation_history' => 'json',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appeal) {
            if (empty($appeal->uuid)) {
                $appeal->uuid = \Illuminate\Support\Str::uuid();
            }
            if (empty($appeal->submitted_at)) {
                $appeal->submitted_at = now();
            }
        });
    }

    /**
     * Relationships
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function appellant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appellant_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'resolved_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AppealDocument::class, 'appeal_id');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', AppealStatus::SUBMITTED);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', AppealStatus::UNDER_REVIEW);
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('status', [AppealStatus::APPROVED, AppealStatus::REJECTED, AppealStatus::DISMISSED]);
    }

    public function scopeByPriority($query, AppealPriority $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline_at', '<', now())
                    ->whereNotIn('status', [AppealStatus::APPROVED, AppealStatus::REJECTED, AppealStatus::DISMISSED]);
    }

    /**
     * Helper methods
     */
    public function isOverdue(): bool
    {
        return $this->deadline_at && $this->deadline_at->isPast() && !$this->status->isFinal();
    }

    public function canBeAssigned(): bool
    {
        return in_array($this->status, [AppealStatus::SUBMITTED, AppealStatus::UNDER_REVIEW]);
    }

    public function canBeResolved(): bool
    {
        return $this->status === AppealStatus::UNDER_REVIEW;
    }

    public function getStatusColor(): string
    {
        return $this->status->color();
    }

    public function getPriorityColor(): string
    {
        return $this->priority->color();
    }

    public function getDaysUntilDeadline(): ?int
    {
        return $this->deadline_at ? now()->diffInDays($this->deadline_at, false) : null;
    }

    public function getEscalationLevel(): int
    {
        return count($this->escalation_history ?? []);
    }

    public function needsEscalation(): bool
    {
        if ($this->status->isFinal()) {
            return false;
        }

        $escalationTime = $this->priority->escalationTimeHours();
        $hoursSinceAssignment = $this->assigned_at ? $this->assigned_at->diffInHours(now()) : 0;

        return $hoursSinceAssignment >= $escalationTime;
    }
}
