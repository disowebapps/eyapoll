<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\User;
use App\Enums\Notification\NotificationChannel;
use App\Enums\Notification\NotificationStatus;

class Notification extends Model
{
    use HasFactory;

    /**
     * Maximum retry attempts for failed notifications
     */
    private const MAX_RETRY_ATTEMPTS = 3;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'notifiable_type',
        'notifiable_id',
        'type',
        'data',
        'channel',
        'status',
        'sent_at',
        'read_at',
        'failure_reason',
        'retry_count',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'data' => 'json',
            'channel' => NotificationChannel::class,
            'status' => NotificationStatus::class,
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
            'retry_count' => 'integer',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            if (empty($notification->uuid)) {
                $notification->uuid = \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Relationships
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', NotificationStatus::PENDING);
    }

    public function scopeSent($query)
    {
        return $query->where('status', NotificationStatus::SENT);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', NotificationStatus::FAILED);
    }

    public function scopeRead($query)
    {
        return $query->where('status', NotificationStatus::READ);
    }

    public function scopeUnread($query)
    {
        return $query->where('status', NotificationStatus::SENT);
    }

    public function scopeForChannel($query, NotificationChannel $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->whereMorphedTo('notifiable', $user);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeRetryable($query)
    {
        return $query->where('status', NotificationStatus::FAILED)
                    ->where('retry_count', '<', self::MAX_RETRY_ATTEMPTS);
    }

    /**
     * Helper methods
     */
    public function isPending(): bool
    {
        return $this->status === NotificationStatus::PENDING;
    }

    public function isSent(): bool
    {
        return $this->status === NotificationStatus::SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === NotificationStatus::FAILED;
    }

    public function isRead(): bool
    {
        return $this->status === NotificationStatus::READ;
    }

    public function isUnread(): bool
    {
        return $this->status === NotificationStatus::SENT;
    }

    public function canRetry(): bool
    {
        return $this->status->canRetry() && 
               $this->retry_count < self::MAX_RETRY_ATTEMPTS;
    }

    public function canMarkAsRead(): bool
    {
        return $this->status->canMarkAsRead() && 
               $this->channel === NotificationChannel::IN_APP;
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => NotificationStatus::FAILED,
            'failure_reason' => $reason,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    public function markAsRead(): void
    {
        if ($this->canMarkAsRead()) {
            $this->update([
                'status' => NotificationStatus::READ,
                'read_at' => now(),
            ]);
        }
    }

    public function getStatusColor(): string
    {
        return $this->status->color();
    }

    public function getChannelIcon(): string
    {
        return $this->channel->icon();
    }

    public function getTypeLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->type));
    }

    public function getTimeSince(): string
    {
        if ($this->sent_at) {
            return $this->sent_at->diffForHumans();
        }
        
        return $this->created_at->diffForHumans();
    }

    public function getDeliveryTime(): ?string
    {
        if (!$this->sent_at) {
            return null;
        }

        $deliveryTime = $this->sent_at->diffInSeconds($this->created_at);
        
        if ($deliveryTime < 60) {
            return $deliveryTime . ' seconds';
        } elseif ($deliveryTime < 3600) {
            return round($deliveryTime / 60) . ' minutes';
        } else {
            return round($deliveryTime / 3600, 1) . ' hours';
        }
    }

    public function getRetryDelay(): int
    {
        $baseDelay = $this->channel->retryDelayMinutes();
        $backoffMultiplier = config('notifications.delivery.retry_backoff_multiplier', 2);
        
        return $baseDelay * pow($backoffMultiplier, $this->retry_count);
    }

    public function shouldRetry(): bool
    {
        return $this->canRetry() && 
               $this->created_at->addMinutes($this->getRetryDelay())->isPast();
    }

    /**
     * Static methods
     */
    public static function getStatistics(): array
    {
        $total = static::count();
        $sent = static::sent()->count();
        $failed = static::failed()->count();
        $pending = static::pending()->count();
        $read = static::read()->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'read' => $read,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
            'read_rate' => $sent > 0 ? round(($read / $sent) * 100, 2) : 0,
        ];
    }

    public static function getChannelStatistics(): array
    {
        $channels = NotificationChannel::cases();
        $stats = [];

        foreach ($channels as $channel) {
            $channelNotifications = static::forChannel($channel);
            
            $stats[$channel->value] = [
                'total' => $channelNotifications->count(),
                'sent' => $channelNotifications->sent()->count(),
                'failed' => $channelNotifications->failed()->count(),
                'pending' => $channelNotifications->pending()->count(),
            ];
        }

        return $stats;
    }
}