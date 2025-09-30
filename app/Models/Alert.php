<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\AlertType;
use App\Enums\AlertPriority;

class Alert extends Model
{
    protected $fillable = [
        'type',
        'priority',
        'title',
        'message',
        'data',
        'user_id',
        'election_id',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'type' => AlertType::class,
        'priority' => AlertPriority::class,
        'data' => 'json',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election\Election::class);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }
}