<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\Auth\UserStatus;
use App\Models\Election\Election;
use App\Models\Voting\VoteToken;
use App\Models\Auth\IdDocument;
use App\Models\Audit\AuditLog;
use App\Models\ConsensusApproval;
use App\Models\Admin;
use App\Models\MFASetting;
use Carbon\Carbon;



/**
 * @method bool hasExceededResubmissionLimit()
 * @method int getRemainingResubmissionAttempts()
 * @method bool hasPendingKycDocument()
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public int $id;
    public Carbon $created_at;
    public ?int $verification_attempts;
    public ?Carbon $last_verification_attempt;
    public ?float $face_match_score;
    public ?bool $address_verified;
    public ?bool $background_check_completed;
    public ?string $background_check_status;
    public ?string $background_check_results;
    public ?string $compliance_status;
    public ?string $risk_factors;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'email',
        'phone_number',
        'password',
        'first_name',
        'last_name',
        'city',
        'occupation',
        'about_me',
        'bio',
        'achievements',
        'current_position',
        'skills',
        'highest_qualification',
        'field_of_study',
        'employment_status',
        'student_status',
        'marital_status',
        'date_of_birth',
        'location_type',
        'abroad_city',
        'linkedin_handle',
        'twitter_handle',
        'instagram_handle',
        'facebook_handle',
        'is_public',
        'email_public',
        'phone_public',
        'is_executive',
        'executive_order',
        'term_start',
        'term_end',
        'profile_image',
        'id_number_hash',
        'id_salt',
        'status',
        'role',
        'verification_data',
        'email_verified_at',
        'phone_verified_at',
        'approved_at',
        'approved_by',
        'suspended_at',
        'suspended_by',
        'suspension_reason',
        'rejection_count',
        'hold_until',
        'expiry_date',
        'renewal_deadline',
        'verification_attempts',
        'last_verification_attempt',
        'face_match_score',
        'address_verified',
        'background_check_completed',
        'background_check_status',
        'background_check_results',
        'compliance_status',
        'risk_factors',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'id_number_hash',
        'id_salt',
        'verification_data',
    ];

    protected $appends = ['is_identity_verified', 'profile_image_url'];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'role' => \App\Enums\Auth\UserRole::class,
            'verification_data' => 'json',
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
            'hold_until' => 'datetime',
            'expiry_date' => 'datetime',
            'renewal_deadline' => 'datetime',
            'date_of_birth' => 'date',
            'term_start' => 'date',
            'term_end' => 'date',
            'is_public' => 'boolean',
            'email_public' => 'boolean',
            'phone_public' => 'boolean',
            'is_executive' => 'boolean',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Relationships
     */
    public function idDocuments(): HasMany
    {
        return $this->hasMany(IdDocument::class);
    }



    public function voteTokens(): HasMany
    {
        return $this->hasMany(VoteToken::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'suspended_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function mfaSetting(): HasOne
    {
        return $this->hasOne(MFASetting::class);
    }

    public function consensusApprovals(): HasMany
    {
        return $this->hasMany(ConsensusApproval::class, 'requested_by');
    }



    /**
     * Scopes
     */
    public function scopeWithCommonRelationships($query)
    {
        return $query->with([
            'idDocuments' => function ($q) {
                $q->latestVersion()->select('id', 'user_id', 'status', 'document_type', 'expiry_date');
            },
            'approvedBy:id,name',
            'suspendedBy:id,name'
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', UserStatus::PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', UserStatus::APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', UserStatus::REJECTED);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', UserStatus::SUSPENDED);
    }

    /** @noinspection PhpUndefinedClassConstantInspection */
    public function scopeTemporaryHold($query)
    {
        return $query->where('status', UserStatus::TEMPORARY_HOLD);
    }

    /** @noinspection PhpUndefinedClassConstantInspection */
    public function scopeExpired($query)
    {
        return $query->where('status', UserStatus::EXPIRED);
    }

    /** @noinspection PhpUndefinedClassConstantInspection */
    public function scopeRenewalRequired($query)
    {
        return $query->where('status', UserStatus::RENEWAL_REQUIRED);
    }



    /**
     * Helper methods
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function canLogin(): bool
    {
        return $this->status->canLogin();
    }

    public function canVote(): bool
    {
        // Must be ACCREDITED to vote - this happens after admin distributes vote tokens
        return $this->status === \App\Enums\Auth\UserStatus::ACCREDITED;
    }

    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function isPhoneVerified(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    public function hasVerifiedDocuments(): bool
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "user_verified_docs_{$this->id}",
            1800, // 30 minutes
            fn() => $this->idDocuments()->where('status', 'approved')->exists()
        );
    }

    public function isSuspended(): bool
    {
        return $this->status->value === 'suspended';
    }

    public function isApproved(): bool
    {
        return $this->status->value === 'approved';
    }

    public function getDashboardRoute(): string
    {
        return $this->role->dashboardRoute();
    }

    public function hasActiveCandidateApplications(): bool
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "user_candidate_apps_{$this->id}",
            300, // 5 minutes
            fn() => $this->hasMany(\App\Models\Candidate\Candidate::class)
                ->whereIn('status', ['pending', 'approved'])
                ->exists()
        );
    }

    public function hasApprovedCandidateApplications(): bool
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "user_approved_candidate_apps_{$this->id}",
            300, // 5 minutes
            fn() => $this->hasMany(\App\Models\Candidate\Candidate::class)
                ->where('status', 'approved')
                ->exists()
        );
    }

    public function canAccessCandidateDashboard(): bool
    {
        return $this->role === \App\Enums\Auth\UserRole::CANDIDATE && 
               $this->hasActiveCandidateApplications();
    }

    public function getStatusBadgeColor(): string
    {
        return $this->status->color();
    }



    /**
     * Check if user can vote in a specific election
     */
    public function canVoteInElection(Election $election): bool
    {
        $eligibility = app(\App\Services\Voting\EligibilityService::class)
            ->checkEligibility($this, $election);
        return $eligibility->isEligible();
    }

    /**
     * Check if user has voted in a specific election
     */
    public function hasVotedInElection(Election $election): bool
    {
        $token = $this->voteTokens()->where('election_id', $election->id)->first();

        return $token && $token->is_used;
    }

    /**
     * Get user's vote receipt for an election
     */
    public function getVoteReceiptForElection(\App\Models\Election\Election $election): ?string
    {
        $token = $this->voteTokens()
            ->where('election_id', $election->id)
            ->where('is_used', true)
            ->first();
            
        return $token?->vote_receipt_hash;
    }

    /**
     * Get KYC status for dashboard display
     */
    public function getKycStatus(): array
    {
        return match($this->status) {
            \App\Enums\Auth\UserStatus::APPROVED => [
                'status' => 'approved',
                'text' => 'KYC Approved',
                'subtext' => 'Identity Verified',
                'color' => 'green'
            ],
            \App\Enums\Auth\UserStatus::ACCREDITED => [
                'status' => 'accredited',
                'text' => 'Accredited Voter',
                'subtext' => 'Ready to vote in elections',
                'color' => 'green'
            ],
            \App\Enums\Auth\UserStatus::REVIEW => [
                'status' => 'review',
                'text' => 'KYC Under Review',
                'subtext' => 'Waiting for Admin Approval',
                'color' => 'blue'
            ],
            \App\Enums\Auth\UserStatus::REJECTED => [
                'status' => 'rejected',
                'text' => 'KYC Rejected',
                'subtext' => 'Please upload new documents',
                'color' => 'red'
            ],
            default => [
                'status' => 'required',
                'text' => 'KYC Required',
                'subtext' => 'Please upload your ID documents',
                'color' => 'yellow'
            ]
        };
    }
    
    /**
     * Check if user can upload new KYC documents
     */
    public function canUploadKycDocuments(): bool
    {
        // Can upload only if PENDING or REJECTED status
        $statusAllowsUpload = in_array($this->status, [
            \App\Enums\Auth\UserStatus::PENDING,
            \App\Enums\Auth\UserStatus::REJECTED
        ]);

        // Must not have exceeded resubmission limit
        $withinLimit = !$this->hasExceededResubmissionLimit();

        // Must not have a pending document already
        $noPendingDocument = !$this->hasPendingKycDocument();

        return $statusAllowsUpload && $withinLimit && $noPendingDocument;
    }

    /**
     * Check if user has a pending KYC document
     */
    public function hasPendingKycDocument(): bool
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "user_pending_kyc_{$this->id}",
            300, // 5 minutes
            fn() => $this->idDocuments()->where('status', 'pending')->exists()
        );
    }

    /**
     * Check if user has exceeded the KYC resubmission limit
     */
    public function hasExceededResubmissionLimit(): bool
    {
        return $this->rejection_count >= $this->getMaxKycResubmissions();
    }

    /**
     * Get remaining resubmission attempts
     */
    public function getRemainingResubmissionAttempts(): int
    {
        $maxAttempts = $this->getMaxKycResubmissions();
        return max(0, $maxAttempts - $this->rejection_count);
    }

    /**
     * Get the maximum KYC resubmissions allowed
     */
    private function getMaxKycResubmissions(): int
    {
        return \Illuminate\Support\Facades\Cache::remember('kyc_max_resubmissions', 3600, function() {
            $setting = \Illuminate\Support\Facades\DB::table('system_settings')
                ->where('key', 'max_kyc_resubmissions')
                ->first();

            return $setting ? (int) json_decode($setting->value, true) : 3;
        });
    }

    /**
     * Get account status with appropriate styling
     */
    public function getAccountStatus(): array
    {
        $kycStatus = $this->getKycStatus();
        
        if ($kycStatus['status'] === 'approved') {
            return [
                'status' => 'verified',
                'text' => 'Verified Member',
                'subtext' => 'Awaiting voter register publication',
                'color' => 'blue'
            ];
        }
        
        if ($this->status === \App\Enums\Auth\UserStatus::ACCREDITED) {
            $unusedTokens = $this->voteTokens()->where('is_used', false)->count();
            return [
                'status' => 'accredited',
                'text' => 'Accredited Voter',
                'subtext' => "Eligible to vote in {$unusedTokens} election(s)",
                'color' => 'green'
            ];
        }
        
        return $kycStatus;
    }

    /**
     * Get cached identity verification status
     */
    public function getIsIdentityVerifiedAttribute(): bool
    {
        return \Illuminate\Support\Facades\Cache::remember("user_verified_{$this->id}", 3600, function() {
            $kycCompleted = $this->verification_data['kyc_completed'] ?? false;
            $hasApprovedDocuments = $this->hasVerifiedDocuments();
            $isActiveStatus = $this->status->value === 'approved';
            
            return $kycCompleted || $hasApprovedDocuments || $isActiveStatus;
        });
    }

    /**
     * Get profile image URL with fallback
     */
    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }

        return "https://ui-avatars.com/api/?name={$this->full_name}&size=200&background=3b82f6&color=ffffff";
    }
}
