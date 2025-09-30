<?php

namespace App\Enums\Notification;

enum NotificationAudience: string
{
    case INDIVIDUAL = 'individual';      // Specific user
    case ADMIN = 'admin';               // All admins
    case VOTER = 'voter';               // All voters
    case CANDIDATE = 'candidate';       // All candidates
    case OBSERVER = 'observer';         // All observers
    case ALL_VOTERS = 'all_voters';     // All registered voters
    case ALL_USERS = 'all_users';       // All platform users
    case PUBLIC = 'public';             // Public announcements

    public function label(): string
    {
        return match($this) {
            self::INDIVIDUAL => 'Individual User',
            self::ADMIN => 'Administrators',
            self::VOTER => 'Voters',
            self::CANDIDATE => 'Candidates',
            self::OBSERVER => 'Observers',
            self::ALL_VOTERS => 'All Voters',
            self::ALL_USERS => 'All Users',
            self::PUBLIC => 'Public',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::INDIVIDUAL => 'Send to a specific user only',
            self::ADMIN => 'Send to all administrators',
            self::VOTER => 'Send to all registered voters',
            self::CANDIDATE => 'Send to all candidates',
            self::OBSERVER => 'Send to all observers',
            self::ALL_VOTERS => 'Send to all eligible voters',
            self::ALL_USERS => 'Send to all platform users',
            self::PUBLIC => 'Public announcement (no login required)',
        };
    }

    public function getTargetUsers(): array
    {
        return match($this) {
            self::INDIVIDUAL => [], // Requires specific user ID
            self::ADMIN => ['role' => 'admin'],
            self::VOTER => ['role' => 'voter'],
            self::CANDIDATE => ['role' => 'candidate'],
            self::OBSERVER => ['role' => 'observer'],
            self::ALL_VOTERS => ['can_vote' => true],
            self::ALL_USERS => [], // All users
            self::PUBLIC => [], // No users - public notification
        };
    }
}