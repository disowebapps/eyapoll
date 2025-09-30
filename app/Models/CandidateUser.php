<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Candidate\Candidate;

class CandidateUser extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'candidates_users';

    protected $fillable = [
        'uuid',
        'email',
        'phone_number',
        'password',
        'first_name',
        'last_name',
        'id_number_hash',
        'id_salt',
        'status',
        'verification_data',
        'approved_at',
        'approved_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'id_number_hash',
        'id_salt',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'verification_data' => 'array',
        'password' => 'hashed',
        'status' => \App\Enums\Auth\UserStatus::class,
    ];

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'candidate_user_id');
    }
}