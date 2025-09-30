<?php

namespace App\Services\TokenManagement;

use App\Models\User;
use App\Models\Election\Election;
use App\Models\Voting\VoteToken;
use App\Exceptions\TokenValidationException;

class TokenValidationService
{
    public function validateTokenIssuance(User $user, Election $election): void
    {
        if (!$user->isApproved()) {
            throw new TokenValidationException('User must be approved before token issuance');
        }

        if (!$user->hasVerifiedDocuments()) {
            throw new TokenValidationException('User must have verified KYC documents');
        }

        if ($this->userHasActiveToken($user, $election)) {
            throw new TokenValidationException('User already has an active token for this election');
        }

        if (!$election->canIssueTokens()) {
            throw new TokenValidationException('Election is not in a state that allows token issuance');
        }

        if ($election->hasEnded()) {
            throw new TokenValidationException('Cannot issue tokens for ended elections');
        }
    }

    public function validateTokenRevocation(VoteToken $token): void
    {
        if ($token->is_revoked) {
            throw new TokenValidationException('Token is already revoked');
        }

        if ($token->is_used) {
            throw new TokenValidationException('Cannot revoke used token without special authorization');
        }
    }

    public function validateTokenReassignment(VoteToken $token, User $newUser): void
    {
        if ($token->is_revoked || $token->is_used) {
            throw new TokenValidationException('Only active tokens can be reassigned');
        }

        if (!$newUser->isApproved()) {
            throw new TokenValidationException('New user must be approved');
        }

        if ($this->userHasActiveToken($newUser, $token->election)) {
            throw new TokenValidationException('New user already has a token for this election');
        }

        if ($token->user_id === $newUser->id) {
            throw new TokenValidationException('Cannot reassign token to the same user');
        }
    }

    private function userHasActiveToken(User $user, Election $election): bool
    {
        return VoteToken::where('user_id', $user->id)
            ->where('election_id', $election->id)
            ->where('is_revoked', false)
            ->exists();
    }
}