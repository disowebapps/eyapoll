<?php

namespace App\Validators;

use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Election\Election;
use App\Models\User;
use App\Services\Auth\VerificationService;

class CustomValidators
{
    /**
     * Validate that election dates don't conflict with existing elections
     */
    public static function validateElectionDates($attribute, $value, $parameters, Validator $validator): bool
    {
        $data = $validator->getData();
        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;
        $excludeId = $parameters[0] ?? null;

        if (!$startDate || !$endDate) {
            return true; // Let required rules handle this
        }

        $query = Election::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q) use ($startDate, $endDate) {
                      $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                  });
        });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Validate Nigerian phone number format
     */
    public static function validateNigerianPhone($attribute, $value, $parameters, Validator $validator): bool
    {
        $verificationService = app(VerificationService::class);
        return $verificationService->validatePhoneNumber($value);
    }

    /**
     * Validate ID number format and uniqueness
     */
    public static function validateIdNumber($attribute, $value, $parameters, Validator $validator): bool
    {
        $verificationService = app(VerificationService::class);
        $excludeUserId = $parameters[0] ?? null;

        return $verificationService->validateIdNumber($value) &&
               !$verificationService->checkDuplicateIdNumber($value, $excludeUserId);
    }

    /**
     * Validate election is active for voting
     */
    public static function validateElectionActive($attribute, $value, $parameters, Validator $validator): bool
    {
        $election = Election::find($value);
        return $election && $election->isActive();
    }

    /**
     * Validate user can vote in election
     */
    public static function validateUserCanVoteInElection($attribute, $value, $parameters, Validator $validator): bool
    {
        $data = $validator->getData();
        $electionId = $data['election_id'] ?? null;
        $user = Auth::user();

        if (!$electionId || !$user) {
            return false;
        }

        $election = Election::find($electionId);
        return $election && $user->canVoteInElection($election);
    }

    /**
     * Validate user hasn't already voted in election
     */
    public static function validateUserHasNotVoted($attribute, $value, $parameters, Validator $validator): bool
    {
        $data = $validator->getData();
        $electionId = $data['election_id'] ?? null;
        $user = Auth::user();

        if (!$electionId || !$user) {
            \Illuminate\Support\Facades\Log::info('validateUserHasNotVoted: Missing electionId or user', ['electionId' => $electionId, 'userId' => $user?->id]);
            return false;
        }

        $election = Election::find($electionId);
        if (!$election) {
            \Illuminate\Support\Facades\Log::info('validateUserHasNotVoted: Election not found', ['electionId' => $electionId]);
            return false;
        }

        $hasVoted = $user->hasVotedInElection($election);
        \Illuminate\Support\Facades\Log::info('validateUserHasNotVoted: Called hasVotedInElection', ['userId' => $user->id, 'electionId' => $electionId, 'hasVoted' => $hasVoted]);
        return !$hasVoted;
    }

    /**
     * Validate candidate belongs to position
     */
    public static function validateCandidateBelongsToPosition($attribute, $value, $parameters, Validator $validator): bool
    {
        $data = $validator->getData();
        $positionId = $data['position_id'] ?? null;

        if (!$positionId) {
            return false;
        }

        return \App\Models\Candidate\Candidate::where('id', $value)
            ->where('position_id', $positionId)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Validate position belongs to election
     */
    public static function validatePositionBelongsToElection($attribute, $value, $parameters, Validator $validator): bool
    {
        $data = $validator->getData();
        $electionId = $data['election_id'] ?? null;

        if (!$electionId) {
            return false;
        }

        return \App\Models\Election\Position::where('id', $value)
            ->where('election_id', $electionId)
            ->exists();
    }

    /**
     * Validate election is accepting candidates
     */
    public static function validateElectionAcceptingCandidates($attribute, $value, $parameters, Validator $validator): bool
    {
        $election = Election::find($value);
        return $election && $election->isAcceptingCandidates();
    }

    /**
     * Validate user hasn't already applied for position
     */
    public static function validateUserNotAppliedForPosition($attribute, $value, $parameters, Validator $validator): bool
    {
        $data = $validator->getData();
        $electionId = $data['election_id'] ?? null;
        $user = Auth::user();

        if (!$electionId || !$user) {
            return false;
        }

        return !\App\Models\Candidate\Candidate::where('user_id', $user->id)
            ->where('election_id', $electionId)
            ->where('position_id', $value)
            ->exists();
    }

    /**
     * Validate position has capacity for more candidates
     */
    public static function validatePositionHasCapacity($attribute, $value, $parameters, Validator $validator): bool
    {
        $position = \App\Models\Election\Position::find($value);
        return $position && ($position->candidates()->where('status', 'approved')->count() < $position->max_candidates);
    }

    /**
     * Validate document file security
     */
    public static function validateSecureDocument($attribute, $value, $parameters, Validator $validator): bool
    {
        if (!$value instanceof \Illuminate\Http\UploadedFile) {
            return false;
        }

        $verificationService = app(VerificationService::class);
        $errors = $verificationService->validateDocumentFile($value);

        if (!empty($errors)) {
            $validator->errors()->add($attribute, implode(', ', $errors));
            return false;
        }

        return true;
    }

    /**
     * Validate password strength
     */
    public static function validateStrongPassword($attribute, $value, $parameters, Validator $validator): bool
    {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $value) &&
               strlen($value) >= 8;
    }

    /**
     * Validate age is appropriate for voting
     */
    public static function validateVotingAge($attribute, $value, $parameters, Validator $validator): bool
    {
        try {
            $birthDate = new \DateTime($value);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;

            return $age >= 18 && $age <= 120;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate election duration is reasonable
     */
    public static function validateElectionDuration($attribute, $value, $parameters, Validator $validator): bool
    {
        $data = $validator->getData();
        $startDate = $data['start_date'] ?? null;

        if (!$startDate) {
            return true; // Let other validations handle this
        }

        try {
            $start = new \DateTime($startDate);
            $end = new \DateTime($value);
            $duration = $start->diff($end);

            // Between 1 hour and 30 days
            $hours = $duration->days * 24 + $duration->h;
            return $hours >= 1 && $hours <= 720;
        } catch (\Exception $e) {
            return false;
        }
    }
}