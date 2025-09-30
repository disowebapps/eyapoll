<?php

namespace App\Services;

use App\Models\User;
use App\Models\Election\Election;
use App\Models\Voting\VoteToken;
use App\Enums\Auth\UserStatus;
use App\Enums\Election\ElectionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccreditationService
{
    /**
     * Manually accredit a single user with full security validation
     */
    public function accreditUser(User $user, $adminId): array
    {
        // Pre-validation
        if ($user->status !== UserStatus::APPROVED) {
            throw new \InvalidArgumentException('User must be approved before accreditation');
        }
        
        if (!$user->hasVerifiedDocuments()) {
            throw new \InvalidArgumentException('User must have verified KYC documents');
        }
        
        if ($user->status === UserStatus::ACCREDITED) {
            throw new \InvalidArgumentException('User is already accredited');
        }
        
        return DB::transaction(function () use ($user, $adminId) {
            // Atomic status update with optimistic locking
            $updated = $user->where('id', $user->id)
                ->where('status', UserStatus::APPROVED)
                ->update([
                    'status' => UserStatus::ACCREDITED,
                    'approved_at' => now(),
                    'approved_by' => $adminId
                ]);
            
            if (!$updated) {
                throw new \RuntimeException('Failed to update user status - possible race condition');
            }
            
            // Generate tokens for eligible elections only
            $elections = Election::whereIn('status', [
                    ElectionStatus::UPCOMING->value,
                    ElectionStatus::ONGOING->value
                ])
                ->where('starts_at', '>', now()->subDays(1)) // Not expired
                ->get();
            
            $tokensCreated = 0;
            $errors = [];
            
            foreach ($elections as $election) {
                try {
                    // Use database-level unique constraint to prevent duplicates
                    $token = $user->voteTokens()->create([
                        'election_id' => $election->id,
                        'token_hash' => VoteToken::generateSecureTokenHash($user, $election),
                        'is_used' => false,
                    ]);
                    $tokensCreated++;
                    
                } catch (\Illuminate\Database\QueryException $e) {
                    // Handle duplicate key constraint
                    if ($e->getCode() === '23000') {
                        Log::warning('Duplicate token creation attempted', [
                            'user_id' => $user->id,
                            'election_id' => $election->id
                        ]);
                    } else {
                        $errors[] = "Failed to create token for election {$election->id}";
                    }
                }
            }
            
            // Comprehensive audit logging
            Log::info('User manually accredited', [
                'user_id' => $user->id,
                'admin_id' => $adminId,
                'elections_count' => $elections->count(),
                'tokens_created' => $tokensCreated,
                'errors' => $errors,
                'timestamp' => now()->toISOString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            // Use proper audit logging service
            app(\App\Services\Audit\AuditLogService::class)->log(
                'user_accredited',
                \App\Models\Admin::find($adminId),
                User::class,
                $user->id,
                ['status' => UserStatus::APPROVED->value],
                [
                    'status' => UserStatus::ACCREDITED->value,
                    'tokens_created' => $tokensCreated,
                    'elections' => $elections->pluck('id')->toArray(),
                    'accreditation_method' => 'manual_individual'
                ]
            );
            
            return [
                'success' => true,
                'elections_count' => $elections->count(),
                'tokens_created' => $tokensCreated,
                'errors' => $errors
            ];
        });
    }
    
    /**
     * Bulk accredit all approved users for a specific election
     */
    public function bulkAccreditForElection(Election $election, $adminId): array
    {
        return DB::transaction(function () use ($election, $adminId) {
            // Get all approved users who don't have tokens for this election
            $approvedUsers = User::where('status', UserStatus::APPROVED)
                ->whereDoesntHave('voteTokens', function ($query) use ($election) {
                    $query->where('election_id', $election->id);
                })
                ->get();
            
            $accreditedCount = 0;
            $tokensCreated = 0;
            
            foreach ($approvedUsers as $user) {
                // Update to accredited status
                $user->update(['status' => UserStatus::ACCREDITED]);
                
                // Create vote token
                $user->voteTokens()->create([
                    'election_id' => $election->id,
                    'token_hash' => VoteToken::generateSecureTokenHash($user, $election),
                    'is_used' => false,
                ]);
                
                $accreditedCount++;
                $tokensCreated++;
            }
            
            // Log the bulk accreditation
            Log::info('Bulk accreditation completed', [
                'election_id' => $election->id,
                'admin_id' => $adminId,
                'users_accredited' => $accreditedCount,
                'tokens_created' => $tokensCreated
            ]);
            
            return [
                'success' => true,
                'users_accredited' => $accreditedCount,
                'tokens_created' => $tokensCreated,
                'total_approved' => $approvedUsers->count()
            ];
        });
    }
    
    /**
     * Check if a user is eligible for accreditation
     */
    public function isEligibleForAccreditation(User $user): bool
    {
        // Must be approved and have verified KYC documents
        return $user->status === UserStatus::APPROVED && $user->hasVerifiedDocuments();
    }
    
    /**
     * Get accreditation statistics
     */
    public function getAccreditationStats(): array
    {
        return [
            'total_users' => User::count(),
            'approved_users' => User::where('status', UserStatus::APPROVED)->count(),
            'accredited_users' => User::where('status', UserStatus::ACCREDITED)->count(),
            'pending_accreditation' => User::where('status', UserStatus::APPROVED)->count(),
            'total_vote_tokens' => VoteToken::count(),
            'unused_tokens' => VoteToken::where('is_used', false)->count(),
        ];
    }
}