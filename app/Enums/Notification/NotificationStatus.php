<?php

namespace App\Enums\Notification;

enum NotificationStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
    case READ = 'read';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::SENT => 'Sent',
            self::FAILED => 'Failed',
            self::READ => 'Read',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PENDING => 'Notification is queued for delivery',
            self::SENT => 'Notification has been successfully delivered',
            self::FAILED => 'Notification delivery failed',
            self::READ => 'Notification has been read by recipient',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::SENT => 'green',
            self::FAILED => 'red',
            self::READ => 'blue',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::SENT => 'heroicon-o-paper-airplane',
            self::FAILED => 'heroicon-o-exclamation-triangle',
            self::READ => 'heroicon-o-eye',
        };
    }

    public function canRetry(): bool
    {
        return $this === self::FAILED;
    }

    public function canMarkAsRead(): bool
    {
        return $this === self::SENT;
    }

    public function isDelivered(): bool
    {
        return in_array($this, [self::SENT, self::READ]);
    }

    public function isUnread(): bool
    {
        return $this === self::SENT;
    }

    public function requiresAction(): bool
    {
        return in_array($this, [self::PENDING, self::FAILED]);
    }

    public function isFinalStatus(): bool
    {
        return in_array($this, [self::SENT, self::READ]);
    }

    public function allowsRetry(): bool
    {
        return $this === self::FAILED;
    }

    public function nextPossibleStatuses(): array
    {
        return match($this) {
            self::PENDING => [self::SENT, self::FAILED],
            self::SENT => [self::READ],
            self::FAILED => [self::SENT, self::FAILED], // Can retry
            self::READ => [], // Final state
        };
    }

    public static function getSelectOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function getActiveStatuses(): array
    {
        return [self::PENDING, self::SENT];
    }

    public static function getFailedStatuses(): array
    {
        return [self::FAILED];
    }

    public static function getDeliveredStatuses(): array
    {
        return [self::SENT, self::READ];
    }

    public static function getUnreadStatuses(): array
    {
        return [self::SENT];
    }

    public static function getReadStatuses(): array
    {
        return [self::READ];
    }

    public static function getRetryableStatuses(): array
    {
        return [self::FAILED];
    }
}