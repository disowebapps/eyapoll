<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

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

    public static function log(string $type, string $severity, string $description, array $metadata = []): self
    {
        return self::create([
            'event_type' => $type,
            'severity' => $severity,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata
        ]);
    }

    public function isSecurityIncident(): bool
    {
        return in_array($this->event_type, ['failed_login', 'suspicious_activity', 'unauthorized_access']);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('resolved', false);
    }
}