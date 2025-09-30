<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Admin;
use App\Enums\Auth\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\Auth\UserStatusChanged;
use Carbon\Carbon;

class UserStatusService
{
    /**
     * Valid status transitions
     */
    private const VALID_TRANSITIONS = [
        UserStatus::PENDING => [UserStatus::REVIEW, UserStatus::REJECTED],
        UserStatus::REVIEW => [UserStatus::APPROVED, UserStatus::REJECTED, UserStatus::TEMPORARY_HOLD],
        UserStatus::APPROVED => [UserStatus::ACCREDITED, UserStatus::SUSPENDED, UserStatus::EXPIRED],
        UserStatus::ACCREDITED => [UserStatus::SUSPENDED, UserStatus::EXPIRED, UserStatus::RENEWAL_REQUIRED],
        UserStatus::REJECTED => [UserStatus::PENDING, UserStatus::REVIEW],
        UserStatus::SUSPENDED => [UserStatus::APPROVED, UserStatus::ACCREDITED, UserStatus::REJECTED],
        UserStatus::TEMPORARY_HOLD => [UserStatus::APPROVED, UserStatus::REJECTED, UserStatus::REVIEW],
        UserStatus::EXPIRED => [UserStatus::RENEWAL_REQUIRED, UserStatus::APPROVED],
        UserStatus::RENEWAL_REQUIRED => [UserStatus::APPROVED, UserStatus::REJECTED],
    ];

    /**
     * Transition user status with validation and metadata updates
     */
    public function transitionStatus(
        User $user,
        UserStatus $newStatus,
        Admin $admin,
        array $metadata = []
    ): bool {
        // Validate transition
        if (!$this->isValidTransition($user->status, $newStatus)) {
            Log::warning('Invalid user status transition attempted', [
                'user_id' => $user->id,
                'from' => $user->status->value,
                'to' => $newStatus->value,
                'admin_id' => $admin->id,
            ]);
            throw new \InvalidArgumentException("Invalid status transition from {$user->status->value} to {$newStatus->value}");
        }

        DB::transaction(function () use ($user, $newStatus, $admin, $metadata) {
            $oldStatus = $user->status;

            // Update status and metadata
            $updateData = ['status' => $newStatus];

            // Handle status-specific metadata
            switch ($newStatus) {
                case UserStatus::SUSPENDED:
                    $updateData['suspended_at'] = now();
                    $updateData['suspended_by'] = $admin->id;
                    $updateData['suspension_reason'] = $metadata['reason'] ?? null;
                    break;

                case UserStatus::TEMPORARY_HOLD:
                    $updateData['hold_until'] = $metadata['hold_until'] ?? null;
                    break;

                case UserStatus::EXPIRED:
                    $updateData['expiry_date'] = $metadata['expiry_date'] ?? now();
                    break;

                case UserStatus::RENEWAL_REQUIRED:
                    $updateData['renewal_deadline'] = $metadata['renewal_deadline'] ?? null;
                    break;

                case UserStatus::APPROVED:
                    $updateData['approved_at'] = now();
                    $updateData['approved_by'] = $admin->id;
                    // Clear suspension data
                    $updateData['suspended_at'] = null;
                    $updateData['suspended_by'] = null;
                    $updateData['suspension_reason'] = null;
                    $updateData['hold_until'] = null;
                    break;
            }

            $user->update($updateData);

            // Log the status change
            Log::info('User status changed', [
                'user_id' => $user->id,
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
                'admin_id' => $admin->id,
                'metadata' => $metadata,
            ]);

            // Fire event
            event(new UserStatusChanged($user, $oldStatus, $newStatus, $admin, $metadata));
        });

        return true;
    }

    /**
     * Check if a status transition is valid
     */
    public function isValidTransition(UserStatus $from, UserStatus $to): bool
    {
        return in_array($to, self::VALID_TRANSITIONS[$from] ?? []);
    }

    /**
     * Get valid next statuses for a user
     */
    public function getValidTransitions(User $user): array
    {
        return self::VALID_TRANSITIONS[$user->status] ?? [];
    }

    /**
     * Check if user status needs automatic updates
     */
    public function checkForAutomaticUpdates(User $user): ?UserStatus
    {
        $now = now();

        // Check temporary hold expiry
        if ($user->status === UserStatus::TEMPORARY_HOLD && $user->hold_until && $user->hold_until->isPast()) {
            return UserStatus::REVIEW; // Return to review after hold
        }

        // Check expiry
        if (in_array($user->status, [UserStatus::APPROVED, UserStatus::ACCREDITED]) &&
            $user->expiry_date && $user->expiry_date->isPast()) {
            return UserStatus::EXPIRED;
        }

        // Check renewal deadline
        if ($user->status === UserStatus::RENEWAL_REQUIRED &&
            $user->renewal_deadline && $user->renewal_deadline->isPast()) {
            return UserStatus::EXPIRED; // If renewal deadline passed, expire
        }

        return null;
    }

    /**
     * Process automatic status updates for users
     */
    public function processAutomaticUpdates(): int
    {
        $updatedCount = 0;

        // Find users needing automatic updates
        $usersNeedingUpdate = User::where(function ($query) {
            $now = now();
            $query->where('status', UserStatus::TEMPORARY_HOLD)
                  ->where('hold_until', '<=', $now)
                  ->orWhere(function ($q) use ($now) {
                      $q->whereIn('status', [UserStatus::APPROVED, UserStatus::ACCREDITED])
                        ->where('expiry_date', '<=', $now);
                  })
                  ->orWhere(function ($q) use ($now) {
                      $q->where('status', UserStatus::RENEWAL_REQUIRED)
                        ->where('renewal_deadline', '<=', $now);
                  });
        })->get();

        foreach ($usersNeedingUpdate as $user) {
            $newStatus = $this->checkForAutomaticUpdates($user);
            if ($newStatus) {
                // For automatic updates, we use a system admin or null admin
                $systemAdmin = Admin::where('is_super_admin', true)->first();

                try {
                    $this->transitionStatus($user, $newStatus, $systemAdmin, [
                        'automatic' => true,
                        'reason' => 'Automatic status update based on time conditions'
                    ]);
                    $updatedCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to automatically update user status', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $updatedCount;
    }

    /**
     * Validate status transition metadata
     */
    public function validateTransitionMetadata(UserStatus $newStatus, array $metadata): array
    {
        $errors = [];

        switch ($newStatus) {
            case UserStatus::SUSPENDED:
                if (empty($metadata['reason'])) {
                    $errors[] = 'Suspension reason is required';
                }
                break;

            case UserStatus::TEMPORARY_HOLD:
                if (empty($metadata['hold_until']) || !strtotime($metadata['hold_until'])) {
                    $errors[] = 'Valid hold_until date is required';
                } elseif (Carbon::parse($metadata['hold_until'])->isPast()) {
                    $errors[] = 'Hold until date must be in the future';
                }
                break;

            case UserStatus::EXPIRED:
                if (!empty($metadata['expiry_date']) && !strtotime($metadata['expiry_date'])) {
                    $errors[] = 'Invalid expiry_date format';
                }
                break;

            case UserStatus::RENEWAL_REQUIRED:
                if (empty($metadata['renewal_deadline']) || !strtotime($metadata['renewal_deadline'])) {
                    $errors[] = 'Valid renewal_deadline is required';
                } elseif (Carbon::parse($metadata['renewal_deadline'])->isPast()) {
                    $errors[] = 'Renewal deadline must be in the future';
                }
                break;
        }

        return $errors;
    }
}