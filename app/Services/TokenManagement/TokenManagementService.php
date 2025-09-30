<?php

namespace App\Services\TokenManagement;

use App\Models\User;
use App\Models\Admin;
use App\Models\Election\Election;
use App\Models\Voting\VoteToken;
use App\Events\Token\TokenIssued;
use App\Events\Token\TokenRevoked;
use App\Events\Token\TokenReassigned;
use App\Jobs\BulkTokenOperation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TokenManagementService
{
    public function __construct(
        private TokenValidationService $validator,
        private TokenAuditService $audit
    ) {}

    public function issueToken(User $user, Election $election, User|Admin $admin): array
    {
        
        $this->validator->validateTokenIssuance($user, $election);
        
        return DB::transaction(function () use ($user, $election, $admin) {
            $token = VoteToken::create([
                'user_id' => $user->id,
                'election_id' => $election->id,
                'token_hash' => $this->generateSecureHash($user, $election),
                'issued_by' => $admin->id,
                'issued_at' => now()
            ]);
            
            $user->update(['status' => 'accredited']);
            
            event(new TokenIssued($token, $admin));
            
            return ['success' => true, 'token' => $token];
        });
    }

    public function revokeToken(VoteToken $token, User|Admin $admin, string $reason): array
    {
        
        $this->validator->validateTokenRevocation($token);
        
        return DB::transaction(function () use ($token, $admin, $reason) {
            $token->update([
                'is_revoked' => true,
                'revoked_by' => $admin->id,
                'revoked_at' => now()
            ]);
            
            event(new TokenRevoked($token, $admin, $reason));
            
            return ['success' => true];
        });
    }

    public function reassignToken(VoteToken $token, User $newUser, User|Admin $admin, string $reason): array
    {
        
        $this->validator->validateTokenReassignment($token, $newUser);
        
        return DB::transaction(function () use ($token, $newUser, $admin, $reason) {
            $oldUserId = $token->user_id;
            
            $token->update([
                'user_id' => $newUser->id,
                'token_hash' => $this->generateSecureHash($newUser, $token->election),
                'reassigned_by' => $admin->id,
                'reassigned_at' => now(),
                'reassignment_reason' => $reason,
                'previous_user_id' => $oldUserId
            ]);
            
            event(new TokenReassigned($token, $admin, $oldUserId, $reason));
            
            return ['success' => true];
        });
    }

    public function bulkIssueTokens(array $userIds, Election $election, User|Admin $admin): void
    {
        
        BulkTokenOperation::dispatch('issue', $userIds, $election->id, $admin->id);
    }

    private function generateSecureHash(User $user, Election $election): string
    {
        return hash('sha256', implode('|', [
            $user->id,
            $election->id,
            now()->timestamp,
            config('app.key'),
            random_bytes(32)
        ]));
    }
}