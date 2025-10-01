<?php

namespace App\Services\Verification;

use App\Models\User;
use App\Models\Auth\IdDocument;
use App\Enums\Auth\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KycService
{
    public function uploadDocument(User $user, array $data): IdDocument
    {
        $this->validateUploadEligibility($user);
        
        return DB::transaction(function () use ($user, $data) {
            // Upload document
            $document = app(\App\Services\Auth\AuthService::class)
                ->uploadIdDocument($user, $data);
            
            // Atomic status transition
            $this->transitionToReview($user, $document);
            
            return $document;
        });
    }
    
    public function approveUser(User $user, $admin, ?string $reason = null): void
    {
        $this->validateStatusTransition($user, UserStatus::APPROVED);
        
        DB::transaction(function () use ($user, $admin, $reason) {
            $oldStatus = $user->status;
            
            $user->update([
                'status' => UserStatus::APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);
            
            $user->idDocuments()->pending()->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
            
            $this->logStatusChange($user, $oldStatus, UserStatus::APPROVED, $admin, $reason);
            
            // Send approval notification
            app(\App\Services\Notification\NotificationService::class)
                ->sendUserApprovalNotification($user);
        });
    }
    
    public function rejectUser(User $user, $admin, string $reason): void
    {
        $this->validateStatusTransition($user, UserStatus::REJECTED);

        DB::transaction(function () use ($user, $admin, $reason) {
            $oldStatus = $user->status;

            $user->update([
                'status' => UserStatus::REJECTED,
                'rejection_count' => $user->rejection_count + 1
            ]);

            $user->idDocuments()->pending()->update([
                'status' => 'rejected',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->logStatusChange($user, $oldStatus, UserStatus::REJECTED, $admin, $reason);
        });
    }
    
    private function validateUploadEligibility(User $user): void
    {
        if (!in_array($user->status, [UserStatus::PENDING, UserStatus::REJECTED])) {
            throw new \InvalidArgumentException('User cannot upload documents in current status');
        }

        if ($user->hasExceededResubmissionLimit()) {
            throw new \InvalidArgumentException('Maximum resubmission attempts exceeded. Please contact support.');
        }

        if ($user->status === UserStatus::PENDING && $user->idDocuments()->pending()->exists()) {
            throw new \InvalidArgumentException('Upload already in progress');
        }
    }
    
    private function validateStatusTransition(User $user, UserStatus $newStatus): void
    {
        $validTransitions = [
            UserStatus::PENDING->value => [UserStatus::REVIEW],
            UserStatus::REVIEW->value => [UserStatus::APPROVED, UserStatus::REJECTED],
            UserStatus::REJECTED->value => [UserStatus::REVIEW, UserStatus::APPROVED], // Allow direct approval of rejected users
            UserStatus::APPROVED->value => [UserStatus::ACCREDITED, UserStatus::SUSPENDED],
        ];

        $currentStatus = $user->status->value;
        if (!isset($validTransitions[$currentStatus]) ||
            !in_array($newStatus, $validTransitions[$currentStatus])) {
            throw new \InvalidArgumentException("Invalid status transition: {$currentStatus} → {$newStatus->value}");
        }
    }
    
    private function transitionToReview(User $user, IdDocument $document): void
    {
        if ($user->status === UserStatus::PENDING) {
            $oldStatus = $user->status;
            $user->update(['status' => UserStatus::REVIEW]);
            $this->logStatusChange($user, $oldStatus, UserStatus::REVIEW, null, 'Document uploaded');
        }
    }
    
    private function logStatusChange(User $user, UserStatus $from, UserStatus $to, $admin, ?string $reason): void
    {
        Log::info('KYC status changed', [
            'user_id' => $user->id,
            'from' => $from->value,
            'to' => $to->value,
            'admin_id' => $admin?->id,
            'reason' => $reason,
            'timestamp' => now()
        ]);
    }
}
