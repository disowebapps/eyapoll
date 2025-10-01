<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Authenticatable
{
    /**
     * @property int $id
     * @property string $uuid
     * @property string $email
     * @property string $first_name
     * @property string $last_name
     */
    use HasFactory, Notifiable, SoftDeletes;

    public int $id;

    protected $fillable = [
        'uuid',
        'email',
        'phone_number',
        'password',
        'first_name',
        'last_name',
        'profile_image',
        'status',
        'permissions',
        'is_super_admin',
        'approved_at',
        'approved_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'permissions' => 'array',
        'password' => 'hashed',
        'status' => \App\Enums\Auth\UserStatus::class,
        'is_super_admin' => 'boolean',
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
            : "https://ui-avatars.com/api/?name={$this->full_name}&size=200&background=4f46e5&color=ffffff";
    }

    public function hasPermission(string $permission): bool
    {
        // Check if user is marked as super admin in database
        if ($this->is_super_admin) {
            return true;
        }
        
        return in_array($permission, $this->permissions ?? []);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}