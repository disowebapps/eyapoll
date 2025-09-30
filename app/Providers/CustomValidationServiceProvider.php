<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Validators\CustomValidators;

class CustomValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register custom validation rules
        Validator::extend('election_dates_not_conflicting', [CustomValidators::class, 'validateElectionDates']);
        Validator::extend('nigerian_phone', [CustomValidators::class, 'validateNigerianPhone']);
        Validator::extend('valid_id_number', [CustomValidators::class, 'validateIdNumber']);
        Validator::extend('election_active', [CustomValidators::class, 'validateElectionActive']);
        Validator::extend('user_can_vote', [CustomValidators::class, 'validateUserCanVoteInElection']);
        Validator::extend('user_has_not_voted', [CustomValidators::class, 'validateUserHasNotVoted']);
        Validator::extend('candidate_belongs_to_position', [CustomValidators::class, 'validateCandidateBelongsToPosition']);
        Validator::extend('position_belongs_to_election', [CustomValidators::class, 'validatePositionBelongsToElection']);
        Validator::extend('election_accepting_candidates', [CustomValidators::class, 'validateElectionAcceptingCandidates']);
        Validator::extend('user_not_applied', [CustomValidators::class, 'validateUserNotAppliedForPosition']);
        Validator::extend('position_has_capacity', [CustomValidators::class, 'validatePositionHasCapacity']);
        Validator::extend('secure_document', [CustomValidators::class, 'validateSecureDocument']);
        Validator::extend('strong_password', [CustomValidators::class, 'validateStrongPassword']);
        Validator::extend('voting_age', [CustomValidators::class, 'validateVotingAge']);
        Validator::extend('election_duration', [CustomValidators::class, 'validateElectionDuration']);

        // Register custom validation messages
        Validator::replacer('election_dates_not_conflicting', function ($message, $attribute, $rule, $parameters) {
            return 'The election dates conflict with an existing election.';
        });

        Validator::replacer('nigerian_phone', function ($message, $attribute, $rule, $parameters) {
            return 'Please enter a valid Nigerian phone number.';
        });

        Validator::replacer('valid_id_number', function ($message, $attribute, $rule, $parameters) {
            return 'The ID number is invalid or already in use.';
        });

        Validator::replacer('election_active', function ($message, $attribute, $rule, $parameters) {
            return 'The election is not currently active.';
        });

        Validator::replacer('user_can_vote', function ($message, $attribute, $rule, $parameters) {
            return 'You are not eligible to vote in this election.';
        });

        Validator::replacer('user_has_not_voted', function ($message, $attribute, $rule, $parameters) {
            return 'You have already voted in this election.';
        });

        Validator::replacer('candidate_belongs_to_position', function ($message, $attribute, $rule, $parameters) {
            return 'The selected candidate does not belong to this position.';
        });

        Validator::replacer('position_belongs_to_election', function ($message, $attribute, $rule, $parameters) {
            return 'The selected position does not belong to this election.';
        });

        Validator::replacer('election_accepting_candidates', function ($message, $attribute, $rule, $parameters) {
            return 'This election is not accepting candidate applications.';
        });

        Validator::replacer('user_not_applied', function ($message, $attribute, $rule, $parameters) {
            return 'You have already applied for this position.';
        });

        Validator::replacer('position_has_capacity', function ($message, $attribute, $rule, $parameters) {
            return 'This position has reached its maximum number of candidates.';
        });

        Validator::replacer('secure_document', function ($message, $attribute, $rule, $parameters) {
            return 'The uploaded document failed security validation.';
        });

        Validator::replacer('strong_password', function ($message, $attribute, $rule, $parameters) {
            return 'Password must contain at least 8 characters, including uppercase, lowercase, number, and special character.';
        });

        Validator::replacer('voting_age', function ($message, $attribute, $rule, $parameters) {
            return 'You must be at least 18 years old to register.';
        });

        Validator::replacer('election_duration', function ($message, $attribute, $rule, $parameters) {
            return 'Election duration must be between 1 hour and 30 days.';
        });
    }
}