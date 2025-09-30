<?php

namespace App\Enums\Appeal;

enum AppealStatus: string
{
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case DISMISSED = 'dismissed';

    public function label(): string
    {
        return match($this) {
            self::SUBMITTED => 'Submitted',
            self::UNDER_REVIEW => 'Under Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::DISMISSED => 'Dismissed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::SUBMITTED => 'blue',
            self::UNDER_REVIEW => 'yellow',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
            self::DISMISSED => 'gray',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::SUBMITTED => in_array($newStatus, [self::UNDER_REVIEW, self::DISMISSED]),
            self::UNDER_REVIEW => in_array($newStatus, [self::APPROVED, self::REJECTED, self::DISMISSED]),
            self::APPROVED, self::REJECTED, self::DISMISSED => false,
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::DISMISSED]);
    }

    public static function getSelectOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}