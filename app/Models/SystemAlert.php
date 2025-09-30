<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAlert extends Model
{
    protected $fillable = [
        'alert_type',
        'severity',
        'title',
        'message',
        'metadata',
        'acknowledged',
        'acknowledged_at',
        'acknowledged_by'
    ];

    protected $casts = [
        'metadata' => 'array',
        'acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime'
    ];

    public function acknowledgedByUser()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }
}