<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SystemAlert extends Model
{
    protected $fillable = [
        'alert_type',
        'severity',
        'status',
        'message',
        'context',
        'resolved_at',
        'resolved_by'
    ];

    protected $casts = [
        'context' => 'array',
        'resolved_at' => 'datetime'
    ];

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Accessor methods
    public function getAlertTypeAttribute($value)
    {
        return $value;
    }

    public function getSeverityLevelAttribute()
    {
        return match($this->severity) {
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4,
            default => 0
        };
    }

    public function canBeResolved(): bool
    {
        return $this->status === 'active';
    }

    public function resolve(int $resolverId): void
    {
        if (!$this->canBeResolved()) {
            throw new \DomainException('Alert is not in a resolvable state');
        }

        $this->status = 'resolved';
        $this->resolved_at = now();
        $this->resolved_by = $resolverId;
        $this->save();
    }

    public function acknowledge(): void
    {
        if ($this->status === 'active') {
            $this->status = 'acknowledged';
            $this->save();
        }
    }

    // Business invariants
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($alert) {
            $validSeverities = ['low', 'medium', 'high', 'critical'];
            if (!in_array($alert->severity, $validSeverities)) {
                throw new \InvalidArgumentException('Invalid alert severity');
            }

            $validStatuses = ['active', 'acknowledged', 'resolved'];
            if (!in_array($alert->status, $validStatuses)) {
                throw new \InvalidArgumentException('Invalid alert status');
            }
        });
    }
}