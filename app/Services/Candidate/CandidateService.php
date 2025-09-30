<?php

namespace App\Services\Candidate;

use App\Models\Candidate\Candidate;
use App\Models\Admin;
use App\Services\Audit\AuditLogService;
use App\Models\Candidate\CandidateActionHistory;
use App\Models\Candidate\PaymentHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class CandidateService
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function getApprovedCandidates(int $positionId): Collection
    {
        return Candidate::where('position_id', $positionId)
            ->approved()
            ->with(['user:id,first_name,last_name'])
            ->select(['id', 'user_id', 'position_id', 'manifesto'])
            ->get();
    }

    public function getPendingCandidates(): LengthAwarePaginator
    {
        return Candidate::pending()
            ->with(['user:id,first_name,last_name', 'election:id,title', 'position:id,title'])
            ->paymentCompleted()
            ->orderBy('candidates.created_at', 'desc')
            ->paginate(20);
    }

    public function approveCandidate(Candidate $candidate, Admin $admin, ?string $reason = null): bool
    {
        if (!$candidate->canBeApproved()) {
            throw new \InvalidArgumentException('Candidate cannot be approved');
        }

        return DB::transaction(function () use ($candidate, $admin, $reason) {
            $oldStatus = $candidate->status;
            
            $success = $candidate->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            if ($success) {
                // Track role change
                $oldRole = $candidate->user->role;
                $candidate->user->update(['role' => \App\Enums\Auth\UserRole::CANDIDATE]);
                
                // Clear cache
                \Illuminate\Support\Facades\Cache::forget("user_candidate_apps_{$candidate->user_id}");
                
                // Broadcast approval event
                broadcast(new \App\Events\CandidateApproved($candidate));
                
                $this->auditLog->log(
                    'candidate_approved',
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    ['status' => $oldStatus->value, 'user_role' => $oldRole->value],
                    ['status' => 'approved', 'reason' => $reason, 'user_role' => 'candidate']
                );
            }

            return $success;
        });
    }

    public function rejectCandidate(Candidate $candidate, Admin $admin, string $reason): bool
    {
        if (!$candidate->canBeRejected()) {
            throw new \InvalidArgumentException('Candidate cannot be rejected');
        }

        return DB::transaction(function () use ($candidate, $admin, $reason) {
            $oldStatus = $candidate->status;
            
            $success = $candidate->update([
                'status' => 'rejected',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            if ($success) {
                $this->auditLog->log(
                    'candidate_rejected',
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    ['status' => $oldStatus->value],
                    ['status' => 'rejected', 'reason' => $reason]
                );
            }

            return $success;
        });
    }

    public function getCandidateResults(int $electionId): array
    {
        $candidates = Candidate::forElection($electionId)
            ->approved()
            ->with(['position:id,title', 'voteTallies', 'user:id,first_name,last_name'])
            ->get()
            ->groupBy('position_id');

        $results = [];
        foreach ($candidates as $positionId => $positionCandidates) {
            $position = $positionCandidates->first()->position;
            $totalVotes = $positionCandidates->sum(fn($c) => $c->getVoteCount());

            $results[] = [
                'position' => [
                    'id' => $position->id,
                    'title' => $position->title,
                ],
                'total_votes' => $totalVotes,
                'candidates' => $positionCandidates->map(function ($candidate) use ($totalVotes) {
                    $votes = $candidate->getVoteCount();
                    return [
                        'id' => $candidate->id,
                        'name' => $candidate->getDisplayName(),
                        'votes' => $votes,
                        'percentage' => $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 2) : 0,
                        'ranking' => $candidate->getRanking(),
                        'is_winner' => $candidate->isWinner(),
                    ];
                })->sortByDesc('votes')->values(),
            ];
        }

        return $results;
    }

    public function suspendCandidate(Candidate $candidate, Admin $admin, string $reason): bool
    {
        $this->validateAdminPermission($admin, 'approve-candidates');
        $this->validateCandidateExists($candidate);
        $this->validateElectionState($candidate, 'suspend');

        if (!$candidate->status->canBeSuspended()) {
            throw new \InvalidArgumentException('Candidate cannot be suspended');
        }

        return DB::transaction(function () use ($candidate, $admin, $reason) {
            $oldStatus = $candidate->status;
            
            $success = $candidate->update([
                'status' => 'suspended',
                'suspended_by' => $admin->id,
                'suspended_at' => now(),
                'suspension_reason' => $reason,
            ]);

            if ($success) {
                CandidateActionHistory::create([
                    'candidate_id' => $candidate->id,
                    'admin_id' => $admin->id,
                    'action' => 'suspended',
                    'reason' => $reason,
                    'previous_status' => $oldStatus->value,
                    'new_status' => 'suspended',
                ]);
                
                $this->updateUserRole($candidate, 'suspended');
                $this->clearUserCache($candidate->user_id);
                
                $this->auditLog->log(
                    'candidate_suspended',
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    ['status' => $oldStatus->value],
                    [
                        'status' => 'suspended',
                        'reason' => $reason,
                        'suspended_by' => $admin->id,
                        'suspended_by_name' => $admin->first_name . ' ' . $admin->last_name,
                        'suspended_at' => now()->toISOString(),
                        'election_id' => $candidate->election_id,
                        'position_id' => $candidate->position_id,
                        'candidate_name' => $candidate->getDisplayName(),
                    ]
                );
            }

            return $success;
        });
    }

    public function unsuspendCandidate(Candidate $candidate, Admin $admin, string $reason): bool
    {
        $this->validateAdminPermission($admin, 'approve-candidates');
        $this->validateCandidateExists($candidate);
        $this->validateElectionState($candidate, 'unsuspend');

        if (!$candidate->status->canBeUnsuspended()) {
            throw new \InvalidArgumentException('Candidate cannot be unsuspended');
        }

        // Edge case: Cannot unsuspend if voting has started
        if ($candidate->election->isActive()) {
            throw new \InvalidArgumentException('Cannot unsuspend candidate during active voting');
        }

        return DB::transaction(function () use ($candidate, $admin, $reason) {
            $suspensionData = [
                'suspended_by' => $candidate->suspended_by,
                'suspended_at' => $candidate->suspended_at,
                'suspension_reason' => $candidate->suspension_reason,
            ];
            
            $success = $candidate->update([
                'status' => 'approved',
                'suspended_by' => null,
                'suspended_at' => null,
                'suspension_reason' => null,
            ]);

            if ($success) {
                CandidateActionHistory::create([
                    'candidate_id' => $candidate->id,
                    'admin_id' => $admin->id,
                    'action' => 'unsuspended',
                    'reason' => $reason,
                    'previous_status' => 'suspended',
                    'new_status' => 'approved',
                ]);
                
                $this->updateUserRole($candidate, 'approved');
                $this->clearUserCache($candidate->user_id);
                
                $this->auditLog->log(
                    'candidate_unsuspended',
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    ['status' => 'suspended'],
                    [
                        'status' => 'approved',
                        'reason' => $reason,
                        'unsuspended_by' => $admin->id,
                        'unsuspended_by_name' => $admin->first_name . ' ' . $admin->last_name,
                        'unsuspended_at' => now()->toISOString(),
                        'previous_suspension' => $suspensionData,
                        'election_id' => $candidate->election_id,
                        'candidate_name' => $candidate->getDisplayName(),
                    ]
                );
            }

            return $success;
        });
    }

    public function disqualifyCandidate(Candidate $candidate, Admin $admin, string $reason): bool
    {
        $this->validateAdminPermission($admin, 'approve-candidates');
        $this->validateCandidateExists($candidate);

        // Can disqualify even during active election (serious violations)
        if (!in_array($candidate->status->value, ['approved', 'pending', 'suspended'])) {
            throw new \InvalidArgumentException('Candidate cannot be disqualified');
        }

        return DB::transaction(function () use ($candidate, $admin, $reason) {
            $oldStatus = $candidate->status;
            
            $success = $candidate->update([
                'status' => 'rejected',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            if ($success) {
                // Revert user role to voter
                $candidate->user->update(['role' => \App\Enums\Auth\UserRole::VOTER]);
                
                // Clear cache
                \Illuminate\Support\Facades\Cache::forget("user_candidate_apps_{$candidate->user_id}");
                
                $this->auditLog->log(
                    'candidate_disqualified',
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    ['status' => $oldStatus->value],
                    ['status' => 'rejected', 'reason' => $reason, 'disqualified_during_election' => $candidate->election->isActive()]
                );
            }

            return $success;
        });
    }

    public function reinstateCandidate(Candidate $candidate, Admin $admin, string $reason): bool
    {
        $this->validateAdminPermission($admin, 'approve-candidates');
        $this->validateCandidateExists($candidate);
        $this->validateElectionState($candidate, 'reinstate');

        // Cannot reinstate if election is active or ended
        if ($candidate->election->isActive() || $candidate->election->isEnded()) {
            throw new \InvalidArgumentException('Cannot reinstate candidate during or after election');
        }

        if ($candidate->status->value !== 'rejected') {
            throw new \InvalidArgumentException('Only rejected candidates can be reinstated');
        }

        return DB::transaction(function () use ($candidate, $admin, $reason) {
            $success = $candidate->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            if ($success) {
                // Restore user role
                $candidate->user->update(['role' => \App\Enums\Auth\UserRole::CANDIDATE]);
                
                // Clear cache
                \Illuminate\Support\Facades\Cache::forget("user_candidate_apps_{$candidate->user_id}");
                
                $this->auditLog->log(
                    'candidate_reinstated',
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    ['status' => 'rejected'],
                    ['status' => 'approved', 'reason' => $reason]
                );
            }

            return $success;
        });
    }

    private function validateAdminPermission(Admin $admin, string $permission): void
    {
        if (!$admin->hasPermission($permission)) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Insufficient permissions');
        }
    }

    private function validateCandidateExists(Candidate $candidate): void
    {
        if (!$candidate->exists || !$candidate->election->exists) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Candidate or election not found');
        }
    }

    private function validateElectionState(Candidate $candidate, string $action): void
    {
        if ($candidate->election->isCancelled()) {
            throw new \InvalidArgumentException('Cannot modify candidate in cancelled election');
        }

        $restrictedActions = ['suspend', 'unsuspend', 'reinstate'];
        if (in_array($action, $restrictedActions) && $candidate->election->isActive()) {
            throw new \InvalidArgumentException("Cannot {$action} candidate during active election");
        }
    }

    private function updateUserRole(Candidate $candidate, string $status): void
    {
        if (!$candidate->user) {
            return;
        }

        $roleMap = [
            'approved' => \App\Enums\Auth\UserRole::CANDIDATE,
            'rejected' => \App\Enums\Auth\UserRole::VOTER,
            'suspended' => \App\Enums\Auth\UserRole::VOTER,
        ];

        if (isset($roleMap[$status])) {
            $candidate->user->update(['role' => $roleMap[$status]]);
        }
    }

    private function clearUserCache(int $userId): void
    {
        \Illuminate\Support\Facades\Cache::forget("user_candidate_apps_{$userId}");
    }

    public function confirmPayment(Candidate $candidate, Admin $admin, string $confirmationDetails): bool
    {
        $this->validateAdminPermission($admin, 'approve-candidates');
        $this->validateCandidateExists($candidate);
        
        if (!$candidate->payment_status || !in_array($candidate->payment_status->value, ['pending', 'failed'])) {
            throw new \InvalidArgumentException('Payment cannot be confirmed for current status');
        }

        return DB::transaction(function () use ($candidate, $admin, $confirmationDetails) {
            $oldStatus = $candidate->payment_status;
            
            $success = $candidate->update(['payment_status' => 'paid']);

            if ($success) {
                PaymentHistory::create([
                    'candidate_id' => $candidate->id,
                    'admin_id' => $admin->id,
                    'action' => 'confirmed',
                    'old_status' => $oldStatus?->value,
                    'new_status' => 'paid',
                    'amount' => $candidate->application_fee,
                    'reason' => $confirmationDetails,
                    'metadata' => ['confirmed_at' => now()]
                ]);
                
                $this->auditLog->log(
                    'payment_confirmed',
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    ['payment_status' => $oldStatus?->value],
                    [
                        'payment_status' => 'paid',
                        'confirmation_details' => $confirmationDetails,
                        'confirmed_by' => $admin->id,
                        'confirmed_by_name' => $admin->first_name . ' ' . $admin->last_name,
                        'confirmed_at' => now()->toISOString(),
                        'application_fee' => $candidate->application_fee,
                    ]
                );
            }

            return $success;
        });
    }

    public function waivePayment(Candidate $candidate, Admin $admin, string $reason): bool
    {
        $this->validateAdminPermission($admin, 'approve-candidates');
        $this->validateCandidateExists($candidate);
        
        if (!$candidate->payment_status || !in_array($candidate->payment_status->value, ['pending', 'failed'])) {
            throw new \InvalidArgumentException('Payment cannot be waived for current status');
        }

        return DB::transaction(function () use ($candidate, $admin, $reason) {
            $oldStatus = $candidate->payment_status;
            
            $success = $candidate->update(['payment_status' => 'waived']);

            if ($success) {
                PaymentHistory::create([
                    'candidate_id' => $candidate->id,
                    'admin_id' => $admin->id,
                    'action' => 'waived',
                    'old_status' => $oldStatus?->value,
                    'new_status' => 'waived',
                    'amount' => $candidate->application_fee,
                    'reason' => $reason,
                    'metadata' => ['waived_at' => now()]
                ]);
                
                $this->auditLog->log(
                    'payment_waived',
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    ['payment_status' => $oldStatus?->value],
                    [
                        'payment_status' => 'waived',
                        'waiver_reason' => $reason,
                        'waived_by' => $admin->id,
                        'waived_by_name' => $admin->first_name . ' ' . $admin->last_name,
                        'waived_at' => now()->toISOString(),
                        'original_fee' => $candidate->application_fee,
                    ]
                );
            }

            return $success;
        });
    }

    public function resetPayment(Candidate $candidate, Admin $admin, string $reason): bool
    {
        $this->validateAdminPermission($admin, 'approve-candidates');
        $this->validateCandidateExists($candidate);
        
        if (!$candidate->payment_status || $candidate->payment_status->value !== 'failed') {
            throw new \InvalidArgumentException('Only failed payments can be reset');
        }

        return DB::transaction(function () use ($candidate, $admin, $reason) {
            $oldStatus = $candidate->payment_status;
            
            $success = $candidate->update(['payment_status' => 'pending']);

            if ($success) {
                PaymentHistory::create([
                    'candidate_id' => $candidate->id,
                    'admin_id' => $admin->id,
                    'action' => 'reset',
                    'old_status' => $oldStatus?->value,
                    'new_status' => 'pending',
                    'amount' => $candidate->application_fee,
                    'reason' => $reason,
                    'metadata' => ['reset_at' => now()]
                ]);
                
                $this->auditLog->log(
                    'payment_reset',
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    ['payment_status' => $oldStatus?->value],
                    [
                        'payment_status' => 'pending',
                        'reset_reason' => $reason,
                        'reset_by' => $admin->id,
                        'reset_by_name' => $admin->first_name . ' ' . $admin->last_name,
                        'reset_at' => now()->toISOString(),
                        'application_fee' => $candidate->application_fee,
                    ]
                );
            }

            return $success;
        });
    }

    public function editCandidate(Candidate $candidate, Admin $admin, array $data): bool
    {
        $this->validateSuperAdminPermission($admin);
        $this->validateCandidateExists($candidate);
        $this->validateCandidateEditability($candidate);
        $this->validateEditData($data);

        return DB::transaction(function () use ($candidate, $admin, $data) {
            $oldData = $candidate->only(['manifesto', 'application_fee']);
            
            $updateData = $this->sanitizeEditData($data);
            
            // Edge case: No actual changes
            if (empty(array_diff_assoc($updateData, $oldData))) {
                return true; // No changes needed
            }

            $success = $candidate->update($updateData);

            if ($success) {
                $this->handlePostEditActions($candidate, $updateData);
                
                $this->auditLog->log(
                    'candidate_edited',
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    $oldData,
                    $updateData
                );
            }

            return $success;
        });
    }

    private function validateCandidateEditability(Candidate $candidate): void
    {
        // Edge case: Cannot edit withdrawn/rejected candidates
        if (in_array($candidate->status->value, ['withdrawn', 'rejected'])) {
            throw new \InvalidArgumentException('Cannot edit withdrawn or rejected candidates');
        }

        // Edge case: Cannot edit during active election (except emergency)
        if ($candidate->election->isActive()) {
            throw new \InvalidArgumentException('Cannot edit candidate during active election');
        }

        // Edge case: Cannot edit after election ended
        if ($candidate->election->isEnded()) {
            throw new \InvalidArgumentException('Cannot edit candidate after election ended');
        }

        // Edge case: Election cancelled
        if ($candidate->election->isCancelled()) {
            throw new \InvalidArgumentException('Cannot edit candidate in cancelled election');
        }
    }

    private function validateEditData(array $data): void
    {
        // Edge case: Empty data
        if (empty($data)) {
            throw new \InvalidArgumentException('No data provided for editing');
        }

        // Edge case: Invalid manifesto
        if (isset($data['manifesto'])) {
            if (!is_string($data['manifesto']) || strlen(trim($data['manifesto'])) < 10) {
                throw new \InvalidArgumentException('Manifesto must be at least 10 characters');
            }
            if (strlen($data['manifesto']) > 5000) {
                throw new \InvalidArgumentException('Manifesto cannot exceed 5000 characters');
            }
        }

        // Edge case: Invalid application fee
        if (isset($data['application_fee'])) {
            if (!is_numeric($data['application_fee']) || $data['application_fee'] < 0) {
                throw new \InvalidArgumentException('Application fee must be a positive number');
            }
            if ($data['application_fee'] > 1000000) {
                throw new \InvalidArgumentException('Application fee cannot exceed 1,000,000');
            }
        }
    }

    private function sanitizeEditData(array $data): array
    {
        $allowedFields = ['manifesto', 'application_fee'];
        $sanitized = array_intersect_key($data, array_flip($allowedFields));
        
        // Sanitize manifesto
        if (isset($sanitized['manifesto'])) {
            $sanitized['manifesto'] = trim(strip_tags($sanitized['manifesto']));
        }
        
        // Sanitize application fee
        if (isset($sanitized['application_fee'])) {
            $sanitized['application_fee'] = round((float)$sanitized['application_fee'], 2);
        }
        
        return $sanitized;
    }

    private function handlePostEditActions(Candidate $candidate, array $updateData): void
    {
        // Edge case: Fee changed - may affect payment status
        if (isset($updateData['application_fee'])) {
            $oldFee = $candidate->getOriginal('application_fee');
            $newFee = $updateData['application_fee'];
            
            // If fee reduced to 0, auto-waive payment
            if ($newFee == 0 && $candidate->payment_status->value === 'pending') {
                $candidate->update(['payment_status' => 'waived']);
            }
            
            // If fee increased and was paid, may need additional payment
            if ($newFee > $oldFee && $candidate->payment_status->value === 'paid') {
                // Log for manual review
                $this->auditLog->log(
                    'candidate_fee_increased_after_payment',
                    null,
                    Candidate::class,
                    $candidate->id,
                    ['old_fee' => $oldFee],
                    ['new_fee' => $newFee, 'requires_review' => true]
                );
            }
        }
        
        // Clear cache after edit
        $this->clearUserCache($candidate->user_id);
    }

    private function validateSuperAdminPermission(Admin $admin): void
    {
        if (!$admin->hasPermission('manage-system-settings')) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Super admin permissions required');
        }
    }

    private function updateCandidateStatus(
        Candidate $candidate, 
        Admin $admin, 
        string $newStatus, 
        ?string $reason, 
        string $auditAction
    ): bool {
        return DB::transaction(function () use ($candidate, $admin, $newStatus, $reason, $auditAction) {
            $oldStatus = $candidate->status;
            
            $updateData = ['status' => $newStatus];
            if ($reason) {
                $updateData['rejection_reason'] = $reason;
            }
            if (in_array($newStatus, ['approved', 'rejected'])) {
                $updateData['approved_by'] = $admin->id;
                $updateData['approved_at'] = now();
            }

            $success = $candidate->update($updateData);

            if ($success) {
                $this->updateUserRole($candidate, $newStatus);
                $this->clearUserCache($candidate->user_id);
                
                $this->auditLog->log(
                    $auditAction,
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    ['status' => $oldStatus->value],
                    array_merge(['status' => $newStatus], $reason ? ['reason' => $reason] : [])
                );
            }

            return $success;
        });
    }
}