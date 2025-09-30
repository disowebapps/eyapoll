<?php

namespace App\Enums\Candidate;

enum CandidateStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::WITHDRAWN => 'Withdrawn',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PENDING => 'Application is under administrative review',
            self::APPROVED => 'Application approved and candidate is eligible to contest',
            self::REJECTED => 'Application was rejected by administrators',
            self::WITHDRAWN => 'Candidate voluntarily withdrew from the election',
            self::SUSPENDED => 'Candidate suspended by administrators',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
            self::WITHDRAWN => 'gray',
            self::SUSPENDED => 'orange',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::APPROVED => 'heroicon-o-check-circle',
            self::REJECTED => 'heroicon-o-x-circle',
            self::WITHDRAWN => 'heroicon-o-arrow-left-on-rectangle',
            self::SUSPENDED => 'heroicon-o-pause-circle',
        };
    }

    public function canWithdraw(): bool
    {
        return in_array($this, [self::PENDING, self::APPROVED]);
    }

    public function canBeApproved(): bool
    {
        return $this === self::PENDING;
    }

    public function canBeRejected(): bool
    {
        return in_array($this, [self::PENDING, self::APPROVED]);
    }

    public function canEditApplication(): bool
    {
        return $this === self::PENDING;
    }

    public function canUploadDocuments(): bool
    {
        return $this === self::PENDING;
    }

    public function isEligibleForElection(): bool
    {
        return $this === self::APPROVED;
    }

    public function canBeSuspended(): bool
    {
        return in_array($this, [self::APPROVED, self::PENDING]);
    }

    public function canBeUnsuspended(): bool
    {
        return $this === self::SUSPENDED;
    }

    public function canReceiveVotes(): bool
    {
        return $this === self::APPROVED;
    }

    public function requiresAdminAction(): bool
    {
        return $this === self::PENDING;
    }

    public function isFinalStatus(): bool
    {
        return in_array($this, [self::REJECTED, self::WITHDRAWN]);
    }

    public function allowsStatusChange(): bool
    {
        return !$this->isFinalStatus();
    }

    public function nextPossibleStatuses(): array
    {
        return match($this) {
            self::PENDING => [self::APPROVED, self::REJECTED, self::WITHDRAWN],
            self::APPROVED => [self::REJECTED, self::WITHDRAWN],
            self::REJECTED => [], // Final state
            self::WITHDRAWN => [], // Final state
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
        return [self::PENDING, self::APPROVED];
    }

    public static function getEligibleStatuses(): array
    {
        return [self::APPROVED];
    }

    public static function getPendingStatuses(): array
    {
        return [self::PENDING];
    }

    public static function getFinalStatuses(): array
    {
        return [self::REJECTED, self::WITHDRAWN];
    }
}