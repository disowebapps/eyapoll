<?php

namespace App\Enums\Election;

enum ElectionPhase: string
{
    case SETUP = 'setup';
    case CANDIDATE_REGISTRATION = 'candidate_registration';
    case CANDIDATE_REGISTRATION_CLOSED = 'candidate_registration_closed';
    case VOTER_REGISTRATION = 'voter_registration';
    case VERIFICATION = 'verification';
    case VOTING = 'voting';
    case COLLATION = 'collation';
    case RESULTS_PUBLISHED = 'results_published';
    case POST_ELECTION = 'post_election';

    public function label(): string
    {
        return match($this) {
            self::SETUP => 'Election Setup',
            self::CANDIDATE_REGISTRATION => 'Candidate Application',
            self::CANDIDATE_REGISTRATION_CLOSED => 'Close Candidate Application',
            self::VOTER_REGISTRATION => 'Voter Registration',
            self::VERIFICATION => 'Verification Complete',
            self::VOTING => 'Voting in Progress',
            self::COLLATION => 'Results Collation',
            self::RESULTS_PUBLISHED => 'Results Published',
            self::POST_ELECTION => 'Post-Election Activities',
        };
    }

    public function canTransitionTo(ElectionPhase $phase): bool
    {
        return match($this) {
            self::SETUP => $phase === self::CANDIDATE_REGISTRATION,
            self::CANDIDATE_REGISTRATION => $phase === self::CANDIDATE_REGISTRATION_CLOSED,
            self::CANDIDATE_REGISTRATION_CLOSED => $phase === self::VOTING,
            self::VOTER_REGISTRATION => false, // Not used - voter registration is continuous
            self::VERIFICATION => $phase === self::VOTING,
            self::VOTING => $phase === self::COLLATION,
            self::COLLATION => $phase === self::RESULTS_PUBLISHED,
            self::RESULTS_PUBLISHED => $phase === self::POST_ELECTION,
            self::POST_ELECTION => false,
        };
    }

    public function allowsVoting(): bool
    {
        return $this === self::VOTING;
    }

    public function allowsCandidateRegistration(): bool
    {
        return in_array($this, [self::SETUP, self::CANDIDATE_REGISTRATION, self::VOTER_REGISTRATION]);
    }

    public function allowsVoterRegistration(): bool
    {
        return in_array($this, [self::VOTER_REGISTRATION, self::VERIFICATION]);
    }
}