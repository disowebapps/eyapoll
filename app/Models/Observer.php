<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Observer extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'email',
        'phone_number',
        'password',
        'first_name',
        'last_name',
        'profile_image',
        'type',
        'organization_name',
        'organization_address',
        'certification_number',
        'status',
        'permissions',
        'observer_privileges',
        'approved_at',
        'approved_by',
        'suspended_at',
        'suspended_by',
        'suspension_reason',
        'revoked_at',
        'revoked_by',
        'revocation_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'status' => \App\Enums\Auth\UserStatus::class,
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'suspended_at' => 'datetime',
        'revoked_at' => 'datetime',
        'permissions' => 'array',
        'observer_privileges' => 'array',
        'password' => 'hashed',
    ];

    protected $appends = ['profile_image_url'];

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get profile image URL with fallback
     */
    public function getProfileImageUrlAttribute(): string
    {
        return $this->profile_image 
            ? \Illuminate\Support\Facades\Storage::url($this->profile_image)
            : "https://ui-avatars.com/api/?name={$this->full_name}&size=200&background=7c3aed&color=ffffff";
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function assignedElections()
    {
        return $this->belongsToMany(\App\Models\Election\Election::class, 'observer_elections');
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    public function hasPrivilege(string $privilege): bool
    {
        return in_array($privilege, $this->observer_privileges ?? []);
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function suspendedBy()
    {
        return $this->belongsTo(Admin::class, 'suspended_by');
    }

    public function revokedBy()
    {
        return $this->belongsTo(Admin::class, 'revoked_by');
    }

    public function isActive(): bool
    {
        return $this->status->value === 'approved' && !$this->suspended_at && !$this->revoked_at;
    }

    public static function getAvailablePrivileges(): array
    {
        return [
            'view_audit_logs',
            'export_audit_logs',
            'view_election_results',
            'view_system_health',
            'view_user_activities',
            'monitor_voting_process',
            'access_real_time_data',
            'generate_reports',
            'view_candidate_data',
            'monitor_system_integrity',
        ];
    }
}