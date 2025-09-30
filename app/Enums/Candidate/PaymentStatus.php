<?php

namespace App\Enums\Candidate;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case WAIVED = 'waived';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Payment Pending',
            self::PAID => 'Payment Completed',
            self::WAIVED => 'Payment Waived',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PENDING => 'Application fee payment is required',
            self::PAID => 'Application fee has been successfully paid',
            self::WAIVED => 'Application fee has been waived by administrators',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::PAID => 'green',
            self::WAIVED => 'blue',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-credit-card',
            self::PAID => 'heroicon-o-check-circle',
            self::WAIVED => 'heroicon-o-gift',
        };
    }

    public function requiresPayment(): bool
    {
        return $this === self::PENDING;
    }

    public function isCompleted(): bool
    {
        return in_array($this, [self::PAID, self::WAIVED]);
    }

    public function canBeWaived(): bool
    {
        return $this === self::PENDING;
    }

    public function canProcessPayment(): bool
    {
        return $this === self::PENDING;
    }

    public function allowsApplicationProgress(): bool
    {
        return $this->isCompleted();
    }

    public function requiresPaymentReference(): bool
    {
        return $this === self::PAID;
    }

    public function requiresAdminApproval(): bool
    {
        return $this === self::WAIVED;
    }

    public static function getSelectOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function getCompletedStatuses(): array
    {
        return [self::PAID, self::WAIVED];
    }

    public static function getPendingStatuses(): array
    {
        return [self::PENDING];
    }
}