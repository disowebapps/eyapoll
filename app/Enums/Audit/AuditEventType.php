<?php

namespace App\Enums\Audit;

enum AuditEventType: string
{
    case CANDIDATE_APPROVED = 'candidate_approved';
    case CANDIDATE_REJECTED = 'candidate_rejected';
    case USER_APPROVED = 'user_approved';
    case ELECTION_CREATED = 'election_created';
    case VOTE_CAST = 'vote_cast';
    case RESULTS_PUBLISHED = 'results_published';

    public function label(): string
    {
        return match($this) {
            self::CANDIDATE_APPROVED => 'Candidate Approved',
            self::CANDIDATE_REJECTED => 'Candidate Rejected',
            self::USER_APPROVED => 'User Approved',
            self::ELECTION_CREATED => 'Election Created',
            self::VOTE_CAST => 'Vote Cast',
            self::RESULTS_PUBLISHED => 'Results Published',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::CANDIDATE_APPROVED => 'A candidate application was approved for an election',
            self::CANDIDATE_REJECTED => 'A candidate application was rejected',
            self::USER_APPROVED => 'A user account was approved by administrator',
            self::ELECTION_CREATED => 'A new election was created in the system',
            self::VOTE_CAST => 'A vote was successfully cast in an election',
            self::RESULTS_PUBLISHED => 'Election results were published publicly',
        };
    }
}