<?php

namespace App\Services\Audit;

use App\Models\Voting\VoteAuthorization;
use App\Models\Voting\VoteRecord;
use Illuminate\Support\Facades\Log;

class AuditService
{
    public function logVoteCast(VoteAuthorization $auth, VoteRecord $vote): void
    {
        Log::info('Vote cast', [
            'election_id' => $vote->election_id,
            'voter_hash' => $auth->voter_hash,
            'receipt_hash' => $vote->receipt_hash,
            'cast_at' => $vote->cast_at
        ]);
    }

    public function logVoteAuthorization($user, $election, VoteAuthorization $auth): void
    {
        Log::info('Vote authorized', [
            'user_id' => $user->id,
            'election_id' => $election->id,
            'auth_id' => $auth->id,
            'expires_at' => $auth->expires_at
        ]);
    }

    public function logVoteAttempt($user, $election, string $status, array $reasons = []): void
    {
        Log::info('Vote attempt', [
            'user_id' => $user->id,
            'election_id' => $election->id,
            'status' => $status,
            'reasons' => $reasons
        ]);
    }

    public function logAuthorizationRecovery($user, $election, VoteAuthorization $auth): void
    {
        Log::info('Authorization recovered', [
            'user_id' => $user->id,
            'election_id' => $election->id,
            'new_auth_id' => $auth->id
        ]);
    }

    public function logAuthorizationExtension(VoteAuthorization $auth, string $type, string $reason): void
    {
        Log::info('Authorization extended', [
            'auth_id' => $auth->id,
            'type' => $type,
            'reason' => $reason,
            'new_expires_at' => $auth->expires_at
        ]);
    }

    public function logVoteActivity(VoteAuthorization $auth, string $action, array $data = []): void
    {
        Log::info('Vote activity', [
            'auth_id' => $auth->id,
            'action' => $action,
            'data' => $data
        ]);
    }
}