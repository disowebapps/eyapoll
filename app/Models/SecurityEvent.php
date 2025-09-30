<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityEvent extends Model
{
    protected $fillable = [
        'event_type',
        'severity',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
        'resolved'
    ];

    protected $casts = [
        'metadata' => 'array',
        'resolved' => 'boolean'
    ];
}