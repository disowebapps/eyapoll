<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Notification\Notification;
use App\Enums\Notification\NotificationChannel;
use App\Enums\Notification\NotificationStatus;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'notification_id',
        'channel',
        'recipient_email',
        'recipient_phone',
        'status',
        'message',
        'error_message',
        'metadata',
        'sent_at',
        'delivered_at',
        'retry_count',
        'provider_response',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'channel' => NotificationChannel::class,
        'status' => NotificationStatus::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($log) {
            if (empty($log->uuid)) {
                $log->uuid = \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Get the notification this log belongs to
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Mark as sent
     */
    public function markAsSent(?string $providerResponse = null): void
    {
        $this->update([
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'provider_response' => $providerResponse,
        ]);
    }

    /**
     * Mark as sent (delivered)
     */
    public function markAsDelivered(?string $providerResponse = null): void
    {
        $this->update([
            'status' => NotificationStatus::SENT,
            'delivered_at' => now(),
            'provider_response' => $providerResponse,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage, ?string $providerResponse = null): void
    {
        $this->update([
            'status' => NotificationStatus::FAILED,
            'error_message' => $errorMessage,
            'provider_response' => $providerResponse,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Check if notification can be retried
     */
    public function canRetry(): bool
    {
        return $this->status === NotificationStatus::FAILED &&
               $this->retry_count < 3; // Max 3 retries
    }

    /**
     * Get delivery time in seconds
     */
    public function getDeliveryTime(): ?int
    {
        if (!$this->sent_at || !$this->delivered_at) {
            return null;
        }

        return $this->sent_at->diffInSeconds($this->delivered_at);
    }

    /**
     * Scope for successful deliveries
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', NotificationStatus::SENT);
    }

    /**
     * Scope for failed deliveries
     */
    public function scopeFailed($query)
    {
        return $query->where('status', NotificationStatus::FAILED);
    }

    /**
     * Scope for specific channel
     */
    public function scopeChannel($query, NotificationChannel $channel)
    {
        return $query->where('channel', $channel->value);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            NotificationStatus::PENDING => 'gray',
            NotificationStatus::SENT => 'green',
            NotificationStatus::FAILED => 'red',
            NotificationStatus::READ => 'blue',
        };
    }

    /**
     * Get status icon for UI
     */
    public function getStatusIcon(): string
    {
        return match($this->status) {
            NotificationStatus::PENDING => 'heroicon-o-clock',
            NotificationStatus::SENT => 'heroicon-o-check-circle',
            NotificationStatus::FAILED => 'heroicon-o-x-circle',
            NotificationStatus::READ => 'heroicon-o-eye',
        };
    }
}
