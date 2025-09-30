<?php

namespace App\Models\Audit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Admin;
use App\Models\CandidateUser;
use App\Models\Observer;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'user_type',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'integrity_hash',
        'previous_hash',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'json',
            'new_values' => 'json',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($auditLog) {
            if (empty($auditLog->uuid)) {
                $auditLog->uuid = \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return match($this->user_type) {
            'admin' => $this->belongsTo(Admin::class, 'user_id'),
            'candidate' => $this->belongsTo(CandidateUser::class, 'user_id'),
            'observer' => $this->belongsTo(Observer::class, 'user_id'),
            default => $this->belongsTo(User::class, 'user_id'),
        };
    }

    public function entity()
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, $userId, $userType = 'voter')
    {
        return $query->where('user_id', $userId)->where('user_type', $userType);
    }

    public function scopeForEntity($query, $entityType, $entityId = null)
    {
        $query = $query->where('entity_type', $entityType);
        
        if ($entityId !== null) {
            $query->where('entity_id', (int) $entityId);
        }
        
        return $query;
    }

    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOrderedByDate($query, $direction = 'desc')
    {
        return $query->orderBy('created_at', $direction);
    }

    public function scopeSystemActions($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeUserActions($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Helper methods
     */
    public function isSystemAction(): bool
    {
        return is_null($this->user_id);
    }

    public function isUserAction(): bool
    {
        return !$this->isSystemAction();
    }

    public function getUserName(): string
    {
        if ($this->isSystemAction()) {
            return 'System';
        }

        if (!$this->user_id || !$this->user_type) {
            return 'Unknown User';
        }

        try {
            $user = match($this->user_type) {
                'admin' => \App\Models\Admin::find($this->user_id),
                'observer' => \App\Models\Observer::find($this->user_id),
                'candidate' => \App\Models\CandidateUser::find($this->user_id),
                default => \App\Models\User::find($this->user_id),
            };

            if (!$user) {
                return 'Unknown User';
            }

            $name = $user->full_name ?? ($user->first_name . ' ' . $user->last_name);
            $roleLabel = match($this->user_type) {
                'admin' => 'Admin',
                'observer' => 'Observer',
                'candidate' => 'Candidate',
                default => 'User',
            };

            return "{$name} ({$roleLabel})";
        } catch (\Exception $e) {
            return 'Unknown User';
        }
    }

    public function getActionLabel(): string
    {
        return match($this->action) {
            'user_registered' => 'User Registration',
            'user_approved' => 'User Approved',
            'user_rejected' => 'User Rejected',
            'user_suspended' => 'User Suspended',
            'email_verified' => 'Email Verified',
            'phone_verified' => 'Phone Verified',
            'id_document_uploaded' => 'ID Document Uploaded',
            'id_document_approved' => 'ID Document Approved',
            'id_document_rejected' => 'ID Document Rejected',
            'election_created' => 'Election Created',
            'election_updated' => 'Election Updated',
            'election_started' => 'Election Started',
            'election_ended' => 'Election Ended',
            'election_cancelled' => 'Election Cancelled',
            'position_created' => 'Position Created',
            'position_updated' => 'Position Updated',
            'candidate_application_submitted' => 'Candidate Application Submitted',
            'candidate_approved' => 'Candidate Approved',
            'candidate_rejected' => 'Candidate Rejected',
            'candidate_withdrawn' => 'Candidate Withdrawn',
            'vote_cast' => 'Vote Cast',
            'vote_tokens_generated' => 'Vote Tokens Generated',
            'notification_sent' => 'Notification Sent',
            'system_setting_updated' => 'System Setting Updated',
            'consensus_approval_requested' => 'Consensus Approval Requested',
            'consensus_approval_granted' => 'Consensus Approval Granted',
            'role_upgraded' => 'Role Upgraded',
            'login_attempt' => 'Login Attempt',
            'login_success' => 'Login Success',
            'login_failed' => 'Login Failed',
            'logout' => 'Logout',
            default => ucwords(str_replace('_', ' ', $this->action)),
        };
    }

    public function getDescription(): string
    {
        $userName = $this->getUserName();
        
        // Get entity name if available
        $entityName = $this->getEntityName();
        
        return match($this->action) {
            'candidate_approved' => "Candidate application approved by {$userName}" . ($entityName ? " for {$entityName}" : ""),
            'candidate_rejected' => "Candidate application rejected by {$userName}" . ($entityName ? " for {$entityName}" : ""),
            'user_approved' => "User account approved by {$userName}" . ($entityName ? " for {$entityName}" : ""),
            'user_rejected' => "User account rejected by {$userName}" . ($entityName ? " for {$entityName}" : ""),
            'election_created' => "New election created by {$userName}" . ($entityName ? ": {$entityName}" : ""),
            'election_updated' => "Election modified by {$userName}" . ($entityName ? ": {$entityName}" : ""),
            'election_started' => "Election started by {$userName}" . ($entityName ? ": {$entityName}" : ""),
            'election_ended' => "Election ended by {$userName}" . ($entityName ? ": {$entityName}" : ""),
            'vote_cast' => "Vote cast in election" . ($entityName ? ": {$entityName}" : ""),
            'user_registered' => "New user registered in the system" . ($entityName ? ": {$entityName}" : ""),
            'role_upgraded' => "User role upgraded by {$userName}" . ($entityName ? " for {$entityName}" : ""),
            'login_success' => "User {$userName} logged in successfully",
            'login_failed' => "Failed login attempt",
            'logout' => "User {$userName} logged out",
            default => $this->getActionLabel() . " performed by {$userName}" . ($entityName ? " on {$entityName}" : "")
        };
    }
    
    private function getEntityName(): ?string
    {
        if (!$this->entity_type || !$this->entity_id) {
            return null;
        }
        
        try {
            return match($this->entity_type) {
                'App\Models\User' => \App\Models\User::find($this->entity_id)?->getFullNameAttribute(),
                'App\Models\Election\Election' => \App\Models\Election\Election::find($this->entity_id)?->title,
                'App\Models\Candidate\Candidate' => \App\Models\Candidate\Candidate::with('user')->find($this->entity_id)?->getDisplayName(),
                default => "ID: {$this->entity_id}"
            };
        } catch (\Exception $e) {
            return "ID: {$this->entity_id}";
        }
    }

    public function getActionColor(): string
    {
        return match($this->action) {
            'user_approved', 'candidate_approved', 'election_started', 'vote_cast', 'login_success' => 'green',
            'user_rejected', 'candidate_rejected', 'election_cancelled', 'login_failed' => 'red',
            'user_suspended', 'election_ended', 'candidate_withdrawn' => 'orange',
            'user_registered', 'election_created', 'candidate_application_submitted' => 'blue',
            default => 'gray',
        };
    }

    public function getActionIcon(): string
    {
        return match($this->action) {
            'user_registered', 'user_approved' => 'heroicon-o-user-plus',
            'user_rejected', 'user_suspended' => 'heroicon-o-user-minus',
            'election_created' => 'heroicon-o-plus-circle',
            'election_started' => 'heroicon-o-play',
            'election_ended' => 'heroicon-o-stop',
            'election_cancelled' => 'heroicon-o-x-circle',
            'vote_cast' => 'heroicon-o-check-circle',
            'candidate_application_submitted' => 'heroicon-o-document-plus',
            'candidate_approved' => 'heroicon-o-check-badge',
            'candidate_rejected' => 'heroicon-o-x-mark',
            'login_success' => 'heroicon-o-arrow-right-on-rectangle',
            'login_failed' => 'heroicon-o-exclamation-triangle',
            'logout' => 'heroicon-o-arrow-left-on-rectangle',
            default => 'heroicon-o-information-circle',
        };
    }

    public function hasDataChanges(): bool
    {
        return !empty($this->old_values) || !empty($this->new_values);
    }

    public function getChangeSummary(): array
    {
        if (!$this->hasDataChanges()) {
            return [];
        }

        $changes = [];
        $oldValues = $this->old_values ?? [];
        $newValues = $this->new_values ?? [];

        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

        foreach ($allKeys as $key) {
            $oldValue = $oldValues[$key] ?? null;
            $newValue = $newValues[$key] ?? null;

            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $this->formatChangeValue($key, $oldValue),
                    'new' => $this->formatChangeValue($key, $newValue),
                ];
            }
        }

        return $changes;
    }
    
    private function formatChangeValue(string $key, $value): string
    {
        if (is_null($value)) {
            return 'None';
        }
        
        return match($key) {
            'role', 'user_role' => $this->formatRoleValue($value),
            'role_upgraded' => $this->formatLegacyRoleUpgrade($value),
            'starts_at', 'ends_at' => $this->formatDateValue($value),
            'status' => ucfirst(str_replace('_', ' ', $value)),
            'payment_status' => ucfirst(str_replace('_', ' ', $value)),
            'rejection_reason', 'reason' => $value ?: 'None',
            default => (string) $value
        };
    }
    
    private function formatRoleValue($value): string
    {
        if (is_numeric($value)) {
            return match((int) $value) {
                1 => 'Voter',
                2 => 'Candidate', 
                3 => 'Observer',
                4 => 'Admin',
                default => "Role {$value}"
            };
        }
        
        if (is_string($value)) {
            return match(strtolower($value)) {
                'voter' => 'Voter',
                'candidate' => 'Candidate',
                'observer' => 'Observer', 
                'admin' => 'Admin',
                default => ucfirst(str_replace('_', ' ', $value))
            };
        }
        
        return (string) $value;
    }
    
    private function formatBooleanValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        if ($value === 'true' || $value === '1' || $value === 1) {
            return 'Yes';
        }
        
        if ($value === 'false' || $value === '0' || $value === 0) {
            return 'No';
        }
        
        return (string) $value;
    }
    
    private function formatLegacyRoleUpgrade($value): string
    {
        if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
            return 'Voter → Candidate';
        }
        
        return 'No change';
    }
    
    private function formatDateValue($value): string
    {
        if (!$value) {
            return 'None';
        }
        
        try {
            // Handle ISO format dates
            if (str_contains($value, 'T') && str_contains($value, 'Z')) {
                return \Carbon\Carbon::parse($value)->format('M j, Y g:i A');
            }
            
            // Handle datetime-local format
            if (str_contains($value, 'T')) {
                return \Carbon\Carbon::parse($value)->format('M j, Y g:i A');
            }
            
            // Handle regular date format
            return \Carbon\Carbon::parse($value)->format('M j, Y g:i A');
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    public function getEntityLink(): ?string
    {
        if (!$this->entity_type || !$this->entity_id) {
            return null;
        }

        $allowedRoutes = [
            'App\Models\User' => 'admin.users.show',
            'App\Models\Election\Election' => 'admin.elections.show', 
            'App\Models\Candidate\Candidate' => 'admin.candidates.show'
        ];
        
        if (!isset($allowedRoutes[$this->entity_type]) || !is_numeric($this->entity_id)) {
            return null;
        }
        
        return route($allowedRoutes[$this->entity_type], (int) $this->entity_id);
    }

    public function verifyIntegrity(): bool
    {
        $cryptoService = app(\App\Services\Cryptographic\CryptographicService::class);
        
        $logData = [
            'user_id' => $this->user_id,
            'action' => $this->action,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'created_at' => $this->created_at->toISOString(),
            'ip_address' => $this->ip_address,
        ];

        $expectedHash = $cryptoService->generateAuditHash($logData, $this->previous_hash);
        
        return $this->integrity_hash === $expectedHash;
    }

    /**
     * Static methods
     */
    public static function logAction(
        string $action,
        $user = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $cryptoService = app(\App\Services\Cryptographic\CryptographicService::class);

        // Get the last audit log for chain linking
        $lastLog = static::orderBy('created_at', 'desc')->first();
        $previousHash = $lastLog?->integrity_hash;

        // Determine user type
        $userId = null;
        $userType = null;
        if ($user) {
            $userId = $user->id;
            $userType = match(get_class($user)) {
                Admin::class => 'admin',
                CandidateUser::class => 'candidate',
                Observer::class => 'observer',
                default => 'voter',
            };
        }

        $logData = [
            'user_id' => $userId,
            'user_type' => $userType,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()->toISOString(),
        ];

        $integrityHash = $cryptoService->generateAuditHash($logData, $previousHash);

        return static::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'user_id' => $userId,
            'user_type' => $userType,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'integrity_hash' => $integrityHash,
            'previous_hash' => $previousHash,
        ]);
    }

    public static function verifyChainIntegrity(): bool
    {
        $logs = static::orderBy('created_at')->get();
        
        foreach ($logs as $log) {
            if (!$log->verifyIntegrity()) {
                return false;
            }
        }
        
        return true;
    }

    public static function getStatistics(): array
    {
        $total = static::count();
        $userActions = static::userActions()->count();
        $systemActions = static::systemActions()->count();
        $recentActions = static::recent(7)->count();

        return [
            'total_logs' => $total,
            'user_actions' => $userActions,
            'system_actions' => $systemActions,
            'recent_actions' => $recentActions,
            'chain_integrity' => static::verifyChainIntegrity(),
        ];
    }
}