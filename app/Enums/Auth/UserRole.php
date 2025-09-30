<?php

namespace App\Enums\Auth;

enum UserRole: string
{
    case VOTER = 'voter';
    case CANDIDATE = 'candidate';
    case ADMIN = 'admin';
    case OBSERVER = 'observer';

    public function label(): string
    {
        return match($this) {
            self::VOTER => 'Voter',
            self::CANDIDATE => 'Candidate',
            self::ADMIN => 'Administrator',
            self::OBSERVER => 'Observer',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::VOTER => [
                'cast-vote',
                'view-receipt',
                'view-results',
            ],
            self::CANDIDATE => [
                'cast-vote',
                'view-receipt',
                'view-results',
                'apply-as-candidate',
                'view-candidate-dashboard',
                'upload-candidate-documents',
            ],
            self::ADMIN => [
                'manage-elections',
                'manage-users',
                'approve-users',
                'approve-candidates',
                'view-audit-logs',
                'manage-system-settings',
                'send-notifications',
                'export-data',
            ],
            self::OBSERVER => [
                'view-audit-logs',
                'view-results',
                'export-audit-data',
                'view-statistics',
            ],
        };
    }

    public function canManageElections(): bool
    {
        return $this === self::ADMIN;
    }

    public function canApproveUsers(): bool
    {
        return $this === self::ADMIN;
    }

    public function canVote(): bool
    {
        return in_array($this, [self::VOTER, self::CANDIDATE]);
    }

    public function canApplyAsCandidate(): bool
    {
        return in_array($this, [self::VOTER, self::CANDIDATE]);
    }

    public function canViewAuditLogs(): bool
    {
        return in_array($this, [self::ADMIN, self::OBSERVER]);
    }

    public function dashboardRoute(): string
    {
        return match($this) {
            self::VOTER => 'voter.dashboard',
            self::CANDIDATE => 'candidate.dashboard',
            self::ADMIN => 'admin.dashboard',
            self::OBSERVER => 'observer.dashboard',
        };
    }

    public static function getSelectOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function getPublicRoles(): array
    {
        return [self::VOTER, self::CANDIDATE];
    }

    public static function getAdminRoles(): array
    {
        return [self::ADMIN, self::OBSERVER];
    }
}