<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Election\Election;
use App\Models\Voting\VoteToken;

class TokenPolicy
{
    public function issueToken(User $admin, User $user, Election $election): bool
    {
        return $admin->hasPermission('manage_tokens') && 
               $user->isApproved() && 
               $election->canIssueTokens();
    }

    public function revokeToken(User $admin, VoteToken $token): bool
    {
        return $admin->hasPermission('manage_tokens') && 
               in_array($token->status, ['active', 'used']);
    }

    public function reassignToken(User $admin, VoteToken $token, User $newUser): bool
    {
        return $admin->hasPermission('manage_tokens') && 
               $token->status === 'active' && 
               $newUser->isApproved();
    }

    public function bulkIssueTokens(User $admin, Election $election): bool
    {
        return $admin->hasPermission('manage_tokens') && 
               $admin->hasPermission('bulk_operations') && 
               $election->canIssueTokens();
    }

    public function viewAuditLogs(User $admin): bool
    {
        return $admin->hasPermission('view_audit_logs');
    }
}