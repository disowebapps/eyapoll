<?php

namespace App\Enums\Auth;

enum UserStatus: string
{
    case PENDING = 'pending';
    case REVIEW = 'review';
    case APPROVED = 'approved';
    case ACCREDITED = 'accredited';
    case REJECTED = 'rejected';
    case SUSPENDED = 'suspended';
    case TEMPORARY_HOLD = 'temporary_hold';
    case EXPIRED = 'expired';
    case RENEWAL_REQUIRED = 'renewal_required';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending KYC',
            self::REVIEW => 'Under Review',
            self::APPROVED => 'Verified',
            self::ACCREDITED => 'Accredited',
            self::REJECTED => 'Rejected',
            self::SUSPENDED => 'Suspended',
            self::TEMPORARY_HOLD => 'Temporary Hold',
            self::EXPIRED => 'Expired',
            self::RENEWAL_REQUIRED => 'Renewal Required',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::REVIEW => 'blue',
            self::APPROVED => 'blue',
            self::ACCREDITED => 'green',
            self::REJECTED => 'red',
            self::SUSPENDED => 'orange',
            self::TEMPORARY_HOLD => 'purple',
            self::EXPIRED => 'gray',
            self::RENEWAL_REQUIRED => 'amber',
        };
    }

    public function canLogin(): bool
    {
        return in_array($this, [self::PENDING, self::REVIEW, self::APPROVED, self::ACCREDITED, self::TEMPORARY_HOLD, self::RENEWAL_REQUIRED]);
    }

    public function canVote(): bool
    {
        // Only ACCREDITED users can vote
        return $this === self::ACCREDITED;
    }

    public function canApplyAsCandidate(): bool
    {
        return in_array($this, [self::APPROVED, self::ACCREDITED, self::TEMPORARY_HOLD, self::RENEWAL_REQUIRED]);
    }

    public static function getSelectOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}