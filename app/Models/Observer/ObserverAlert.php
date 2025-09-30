<?php

namespace App\Models\Observer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Observer;
use App\Models\Election\Election;
use App\Models\Admin;

class ObserverAlert extends Model
{
    protected $fillable = [
        'observer_id',
        'election_id',
        'type',
        'severity',
        'status',
        'title',
        'description',
        'evidence',
        'occurred_at',
        'assigned_to',
        'admin_notes',
        'resolved_at',
    ];

    protected $casts = [
        'evidence' => 'array',
        'occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function observer(): BelongsTo
    {
        return $this->belongsTo(Observer::class);
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }
}