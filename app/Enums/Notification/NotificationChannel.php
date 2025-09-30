<?php

namespace App\Enums\Notification;

enum NotificationChannel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case IN_APP = 'in_app';

    public function label(): string
    {
        return match($this) {
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
            self::IN_APP => 'In-App Notification',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::EMAIL => 'Send notification via email',
            self::SMS => 'Send notification via SMS/text message',
            self::IN_APP => 'Display notification within the application',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::EMAIL => 'heroicon-o-envelope',
            self::SMS => 'heroicon-o-device-phone-mobile',
            self::IN_APP => 'heroicon-o-bell',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::EMAIL => 'blue',
            self::SMS => 'green',
            self::IN_APP => 'purple',
        };
    }

    public function requiresExternalService(): bool
    {
        return match($this) {
            self::EMAIL => true,
            self::SMS => true,
            self::IN_APP => false,
        };
    }

    public function supportsRichContent(): bool
    {
        return match($this) {
            self::EMAIL => true,
            self::SMS => false,
            self::IN_APP => true,
        };
    }

    public function hasCharacterLimit(): bool
    {
        return $this === self::SMS;
    }

    public function characterLimit(): ?int
    {
        return match($this) {
            self::EMAIL => null,
            self::SMS => 160,
            self::IN_APP => null,
        };
    }

    public function supportsSubject(): bool
    {
        return match($this) {
            self::EMAIL => true,
            self::SMS => false,
            self::IN_APP => true,
        };
    }

    public function supportsAttachments(): bool
    {
        return match($this) {
            self::EMAIL => true,
            self::SMS => false,
            self::IN_APP => false,
        };
    }

    public function isRealTime(): bool
    {
        return match($this) {
            self::EMAIL => false,
            self::SMS => true,
            self::IN_APP => true,
        };
    }

    public function defaultRetryAttempts(): int
    {
        return match($this) {
            self::EMAIL => 3,
            self::SMS => 2,
            self::IN_APP => 1,
        };
    }

    public function retryDelayMinutes(): int
    {
        return match($this) {
            self::EMAIL => 5,
            self::SMS => 2,
            self::IN_APP => 1,
        };
    }

    public static function getSelectOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function getEnabledChannels(): array
    {
        $enabled = [];
        
        if (config('ayapoll.notification_channels.email', true)) {
            $enabled[] = self::EMAIL;
        }
        
        if (config('ayapoll.notification_channels.sms', false)) {
            $enabled[] = self::SMS;
        }
        
        if (config('ayapoll.notification_channels.in_app', true)) {
            $enabled[] = self::IN_APP;
        }
        
        return $enabled;
    }

    public static function getExternalChannels(): array
    {
        return [self::EMAIL, self::SMS];
    }

    public static function getInternalChannels(): array
    {
        return [self::IN_APP];
    }
}