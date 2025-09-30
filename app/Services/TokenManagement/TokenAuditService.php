<?php

namespace App\Services\TokenManagement;

use App\Models\Voting\VoteToken;
use App\Models\TokenAuditLog;
use App\Models\User;

class TokenAuditService
{
    public function logTokenIssuance(VoteToken $token, User $admin): void
    {
        $this->createAuditLog($token, 'issued', [
            'issued_by' => $admin->id,
            'issued_by_name' => $admin->name,
            'user_id' => $token->user_id,
            'election_id' => $token->election_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    public function logTokenRevocation(VoteToken $token, User $admin, string $reason): void
    {
        $this->createAuditLog($token, 'revoked', [
            'revoked_by' => $admin->id,
            'revoked_by_name' => $admin->name,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    public function logTokenReassignment(VoteToken $token, User $admin, int $oldUserId, string $reason): void
    {
        $this->createAuditLog($token, 'reassigned', [
            'reassigned_by' => $admin->id,
            'reassigned_by_name' => $admin->name,
            'old_user_id' => $oldUserId,
            'new_user_id' => $token->user_id,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    public function logTokenUsage(VoteToken $token): void
    {
        $this->createAuditLog($token, 'used', [
            'used_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    private function createAuditLog(VoteToken $token, string $action, array $metadata): void
    {
        TokenAuditLog::create([
            'token_id' => $token->id,
            'action' => $action,
            'metadata' => $metadata,
            'created_at' => now()
        ]);
    }
}