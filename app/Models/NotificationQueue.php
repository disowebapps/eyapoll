<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Notification\Notification;
use App\Enums\Notification\NotificationChannel;
use App\Enums\Notification\NotificationPriority;

class NotificationQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'notification_id',
        'channel',
        'recipient_id',
        'recipient_email',
        'recipient_phone',
        'priority',
        'max_retries',
        'retry_count',
        'available_at',
        'reserved_at',
        'reserved_by',
        'payload',
        'error_message',
        'failed_at',
    ];

    protected $casts = [
        'available_at' => 'datetime',
        'reserved_at' => 'datetime',
        'failed_at' => 'datetime',
        'payload' => 'array',
        'channel' => NotificationChannel::class,
        'priority' => NotificationPriority::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($queue) {
            if (empty($queue->uuid)) {
                $queue->uuid = \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Get the notification this queue item belongs to
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Get the recipient user (for in-app notifications)
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Check if the job is available for processing
     */
    public function isAvailable(): bool
    {
        return is_null($this->reserved_at) &&
               is_null($this->failed_at) &&
               $this->available_at->isPast();
    }

    /**
     * Check if the job is reserved by a worker
     */
    public function isReserved(): bool
    {
        return !is_null($this->reserved_at) && is_null($this->failed_at);
    }

    /**
     * Check if the job has failed permanently
     */
    public function hasFailed(): bool
    {
        return !is_null($this->failed_at);
    }

    /**
     * Reserve the job for processing
     */
    public function reserve(string $workerId): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        return $this->update([
            'reserved_at' => now(),
            'reserved_by' => $workerId,
        ]);
    }

    /**
     * Release the job reservation
     */
    public function release(): bool
    {
        return $this->update([
            'reserved_at' => null,
            'reserved_by' => null,
        ]);
    }

    /**
     * Mark the job as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);

        // If max retries exceeded, mark as permanently failed
        if ($this->retry_count >= $this->max_retries) {
            $this->update(['failed_at' => now()]);
        } else {
            // Release for retry with exponential backoff
            $this->release();
            $this->update([
                'available_at' => now()->addSeconds(
                    $this->priority->retryDelay() * ($this->retry_count + 1)
                ),
            ]);
        }
    }

    /**
     * Mark the job as completed
     */
    public function markAsCompleted(): void
    {
        // Delete the queue item since it's completed
        $this->delete();
    }

    /**
     * Check if job can be retried
     */
    public function canRetry(): bool
    {
        return !$this->hasFailed() &&
               $this->retry_count < $this->max_retries;
    }

    /**
     * Get the delay before next retry
     */
    public function getRetryDelay(): int
    {
        return $this->priority->retryDelay() * ($this->retry_count + 1);
    }

    /**
     * Scope for available jobs
     */
    public function scopeAvailable($query)
    {
        return $query->whereNull('reserved_at')
                    ->whereNull('failed_at')
                    ->where('available_at', '<=', now());
    }

    /**
     * Scope for reserved jobs
     */
    public function scopeReserved($query)
    {
        return $query->whereNotNull('reserved_at')
                    ->whereNull('failed_at');
    }

    /**
     * Scope for failed jobs
     */
    public function scopeFailed($query)
    {
        return $query->whereNotNull('failed_at');
    }

    /**
     * Scope for specific channel
     */
    public function scopeChannel($query, NotificationChannel $channel)
    {
        return $query->where('channel', $channel->value);
    }

    /**
     * Scope for specific priority
     */
    public function scopePriority($query, NotificationPriority $priority)
    {
        return $query->where('priority', $priority->value);
    }

    /**
     * Scope for jobs ready for retry
     */
    public function scopeReadyForRetry($query)
    {
        return $query->whereNotNull('reserved_at')
                    ->whereNull('failed_at')
                    ->whereColumn('retry_count', '<', 'max_retries');
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColor(): string
    {
        return $this->priority->color();
    }

    /**
     * Get channel icon for UI
     */
    public function getChannelIcon(): string
    {
        return match($this->channel) {
            NotificationChannel::EMAIL => 'heroicon-o-envelope',
            NotificationChannel::SMS => 'heroicon-o-device-phone-mobile',
            NotificationChannel::IN_APP => 'heroicon-o-bell',
        };
    }
}
