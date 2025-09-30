<?php

namespace App\Enums\Election;

enum ElectionStatus: string
{
    case UPCOMING = 'upcoming';
    case ONGOING = 'ongoing';
    case COMPLETED = 'completed';
    case CERTIFIED = 'certified';
    case FINALIZED = 'finalized';
    case ARCHIVED = 'archived';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::UPCOMING => 'Upcoming',
            self::ONGOING => 'Ongoing',
            self::COMPLETED => 'Completed',
            self::CERTIFIED => 'Certified',
            self::FINALIZED => 'Finalized',
            self::ARCHIVED => 'Archived',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::UPCOMING => 'Election is scheduled and ready to start',
            self::ONGOING => 'Election is currently running and accepting votes',
            self::COMPLETED => 'Election has concluded and results are available',
            self::CERTIFIED => 'Election results have been certified by election officials',
            self::FINALIZED => 'Election results are final and cannot be changed',
            self::ARCHIVED => 'Election has been archived for long-term storage',
            self::CANCELLED => 'Election was cancelled before completion',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::UPCOMING => 'blue',
            self::ONGOING => 'green',
            self::COMPLETED => 'purple',
            self::CERTIFIED => 'orange',
            self::FINALIZED => 'indigo',
            self::ARCHIVED => 'gray',
            self::CANCELLED => 'red',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::UPCOMING => 'heroicon-o-calendar',
            self::ONGOING => 'heroicon-o-play',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::CERTIFIED => 'heroicon-o-shield-check',
            self::FINALIZED => 'heroicon-o-lock-closed',
            self::ARCHIVED => 'heroicon-o-archive-box',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }

    public function getBadgeClass(): string
    {
        return match($this) {
            self::UPCOMING => 'bg-blue-100 text-blue-800',
            self::ONGOING => 'bg-green-100 text-green-800',
            self::COMPLETED => 'bg-purple-100 text-purple-800',
            self::CERTIFIED => 'bg-orange-100 text-orange-800',
            self::FINALIZED => 'bg-indigo-100 text-indigo-800',
            self::ARCHIVED => 'bg-gray-100 text-gray-800',
            self::CANCELLED => 'bg-red-100 text-red-800',
        };
    }

    public function canBeEdited(): bool
    {
        return in_array($this, [self::UPCOMING]);
    }

    public function canBeStarted(): bool
    {
        return in_array($this, [self::UPCOMING]);
    }

    public function canBeEnded(): bool
    {
        return in_array($this, [self::ONGOING]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::UPCOMING, self::ONGOING]);
    }

    public function canAcceptVotes(): bool
    {
        return in_array($this, [self::ONGOING]);
    }

    public function canAcceptCandidateApplications(): bool
    {
        return in_array($this, [self::UPCOMING]);
    }

    public function hasResults(): bool
    {
        return in_array($this, [self::COMPLETED, self::CERTIFIED, self::FINALIZED, self::ARCHIVED, self::CANCELLED]);
    }

    public function allowsResultsViewing(): bool
    {
        return in_array($this, [self::COMPLETED, self::CERTIFIED, self::FINALIZED, self::ARCHIVED]);
    }

    public function canPublishResults(): bool
    {
        return in_array($this, [self::COMPLETED]);
    }

    public function canBeCertified(): bool
    {
        return in_array($this, [self::COMPLETED]);
    }

    public function canBeFinalized(): bool
    {
        return in_array($this, [self::CERTIFIED]);
    }

    public function canBeArchived(): bool
    {
        return in_array($this, [self::FINALIZED]);
    }

    public function requiresConsensusToChange(): bool
    {
        return match($this) {
            self::UPCOMING => false, // Can be changed by single admin
            self::ONGOING => true, // Requires consensus to end
            self::COMPLETED => true, // Requires consensus to certify
            self::CERTIFIED => true, // Requires consensus to finalize
            self::FINALIZED => true, // Requires consensus to archive
            self::ARCHIVED => true, // Requires consensus to unarchive
            self::CANCELLED => false, // Final state - no further changes allowed
        };
    }

    public function nextPossibleStatuses(): array
    {
        return match($this) {
            self::UPCOMING => [self::ONGOING, self::CANCELLED],
            self::ONGOING => [self::COMPLETED, self::CANCELLED],
            self::COMPLETED => [self::CERTIFIED],
            self::CERTIFIED => [self::FINALIZED],
            self::FINALIZED => [self::ARCHIVED],
            self::ARCHIVED => [],
            self::CANCELLED => [],
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
        return [self::UPCOMING, self::ONGOING];
    }

    public static function getFinalStatuses(): array
    {
        return [self::FINALIZED, self::ARCHIVED, self::CANCELLED];
    }

    public static function getEditableStatuses(): array
    {
        return [self::UPCOMING];
    }

    public static function getArchivableStatuses(): array
    {
        return [self::FINALIZED];
    }

    public static function getCertifiableStatuses(): array
    {
        return [self::COMPLETED];
    }

    public static function getFinalizableStatuses(): array
    {
        return [self::CERTIFIED];
    }

    public static function getCurrentStatuses(): array
    {
        return [self::UPCOMING, self::ONGOING, self::COMPLETED, self::CERTIFIED, self::FINALIZED];
    }
}