<?php

namespace App\Enums\Notification;

enum NotificationPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match($this) {
            self::LOW => 'Low',
            self::NORMAL => 'Normal',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::LOW => 'gray',
            self::NORMAL => 'blue',
            self::HIGH => 'orange',
            self::URGENT => 'red',
        };
    }

    public function queueDelay(): int
    {
        return match($this) {
            self::URGENT => 0,      // Immediate
            self::HIGH => 30,       // 30 seconds
            self::NORMAL => 300,    // 5 minutes
            self::LOW => 3600,      // 1 hour
        };
    }

    public function maxRetries(): int
    {
        return match($this) {
            self::URGENT => 5,
            self::HIGH => 3,
            self::NORMAL => 2,
            self::LOW => 1,
        };
    }

    public function retryDelay(): int
    {
        return match($this) {
            self::URGENT => 60,     // 1 minute
            self::HIGH => 300,      // 5 minutes
            self::NORMAL => 1800,   // 30 minutes
            self::LOW => 3600,      // 1 hour
        };
    }
}