<?php

namespace App\Services\Appeal;

use App\Models\ElectionAppeal;
use App\Models\AppealDocument;
use App\Models\User;
use App\Models\Admin;
use App\Enums\Appeal\AppealStatus;
use App\Enums\Appeal\AppealType;
use App\Enums\Appeal\AppealPriority;
use App\Services\Cryptographic\CryptographicService;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class AppealService
{
    public function __construct(
        private CryptographicService $crypto,
        private AuditLogService $auditLog
    ) {}

    /**
     * Submit a new appeal
     */
    public function submitAppeal(
        User $appellant,
        int $electionId,
        AppealType $type,
        string $title,
        string $description,
        array $appealData = [],
        ?AppealPriority $priority = null
    ): ElectionAppeal {
        return DB::transaction(function () use ($appellant, $electionId, $type, $title, $description, $appealData, $priority) {
            // Determine priority based on type if not specified
            $priority = $priority ?? $type->defaultPriority();

            // Calculate deadline (default 30 days from election end)
            $deadline = $this->calculateAppealDeadline($electionId);

            $appeal = ElectionAppeal::create([
                'election_id' => $electionId,
                'appellant_id' => $appellant->id,
                'type' => $type,
                'status' => AppealStatus::SUBMITTED,
                'priority' => $priority,
                'title' => $title,
                'description' => $description,
                'appeal_data' => $appealData,
                'deadline_at' => $deadline,
            ]);

            // Generate integrity hash
            $this->generateAppealIntegrityHash($appeal);

            // Log the action
            $this->auditLog->log(
                'appeal_submitted',
                $appellant->id,
                'election_appeals',
                $appeal->id,
                [
                    'election_id' => $electionId,
                    'type' => $type->value,
                    'priority' => $priority->value,
                ]
            );

            Log::info('Appeal submitted', [
                'appeal_id' => $appeal->id,
                'appellant_id' => $appellant->id,
                'election_id' => $electionId,
            ]);

            return $appeal;
        });
    }

    /**
     * Assign appeal to an admin
     */
    public function assignAppeal(ElectionAppeal $appeal, Admin $admin): bool
    {
        if (!$appeal->canBeAssigned()) {
            throw new \InvalidArgumentException('Appeal cannot be assigned in its current status');
        }

        $appeal->update([
            'assigned_to' => $admin->id,
            'assigned_at' => now(),
            'status' => AppealStatus::UNDER_REVIEW,
        ]);

        $this->auditLog->log(
            'appeal_assigned',
            $admin->id,
            'election_appeals',
            $appeal->id,
            ['assigned_to' => $admin->id]
        );

        return true;
    }

    /**
     * Update appeal status
     */
    public function updateStatus(
        ElectionAppeal $appeal,
        AppealStatus $newStatus,
        Admin $admin,
        ?string $reviewNotes = null,
        ?string $resolution = null
    ): bool {
        if (!$appeal->status->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException("Cannot transition from {$appeal->status->value} to {$newStatus->value}");
        }

        $updates = [
            'status' => $newStatus,
            'reviewed_at' => now(),
            'review_notes' => $reviewNotes,
        ];

        if ($newStatus->isFinal()) {
            $updates['resolved_at'] = now();
            $updates['resolved_by'] = $admin->id;
            $updates['resolution'] = $resolution;
        }

        $appeal->update($updates);

        $this->auditLog->log(
            'appeal_status_updated',
            $admin->id,
            'election_appeals',
            $appeal->id,
            [
                'old_status' => $appeal->status->value,
                'new_status' => $newStatus->value,
                'review_notes' => $reviewNotes,
            ]
        );

        return true;
    }

    /**
     * Escalate appeal to higher priority
     */
    public function escalateAppeal(ElectionAppeal $appeal, Admin $admin, string $reason): bool
    {
        $currentPriority = $appeal->priority;
        $newPriority = $this->getNextPriorityLevel($currentPriority);

        if (!$currentPriority->canEscalateTo($newPriority)) {
            throw new \InvalidArgumentException('Appeal cannot be escalated further');
        }

        $escalationHistory = $appeal->escalation_history ?? [];
        $escalationHistory[] = [
            'from_priority' => $currentPriority->value,
            'to_priority' => $newPriority->value,
            'escalated_by' => $admin->id,
            'escalated_at' => now()->toISOString(),
            'reason' => $reason,
        ];

        $appeal->update([
            'priority' => $newPriority,
            'escalation_history' => $escalationHistory,
        ]);

        $this->auditLog->log(
            'appeal_escalated',
            $admin->id,
            'election_appeals',
            $appeal->id,
            [
                'old_priority' => $currentPriority->value,
                'new_priority' => $newPriority->value,
                'reason' => $reason,
            ]
        );

        return true;
    }

    /**
     * Get appeals requiring attention
     */
    public function getAppealsRequiringAttention(): Collection
    {
        return ElectionAppeal::whereIn('status', [AppealStatus::SUBMITTED, AppealStatus::UNDER_REVIEW])
            ->orderByRaw("
                CASE priority
                    WHEN 'critical' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                END
            ")
            ->orderBy('submitted_at')
            ->get();
    }

    /**
     * Get overdue appeals
     */
    public function getOverdueAppeals(): Collection
    {
        return ElectionAppeal::overdue()->get();
    }

    /**
     * Get appeals needing escalation
     */
    public function getAppealsNeedingEscalation(): Collection
    {
        return ElectionAppeal::where('assigned_to', '!=', null)
            ->get()
            ->filter(fn($appeal) => $appeal->needsEscalation());
    }

    /**
     * Validate appeal deadline
     */
    public function validateAppealDeadline(int $electionId): bool
    {
        $deadline = $this->calculateAppealDeadline($electionId);
        return now()->isBefore($deadline);
    }

    /**
     * Calculate appeal deadline for an election
     */
    private function calculateAppealDeadline(int $electionId): \Carbon\Carbon
    {
        // Default 30 days after election ends
        $defaultDays = config('appeals.deadline_days', 30);

        // For now, use a simple calculation. In production, this would check the actual election end date
        return now()->addDays($defaultDays);
    }

    /**
     * Generate integrity hash for appeal
     */
    private function generateAppealIntegrityHash(ElectionAppeal $appeal): void
    {
        $appealData = [
            'uuid' => $appeal->uuid,
            'election_id' => $appeal->election_id,
            'appellant_id' => $appeal->appellant_id,
            'type' => $appeal->type->value,
            'title' => $appeal->title,
            'description' => $appeal->description,
            'submitted_at' => $appeal->submitted_at->toISOString(),
        ];

        $hash = $this->crypto->generateAuditHash($appealData);
        $appeal->update(['integrity_hash' => $hash]);
    }

    /**
     * Get next priority level for escalation
     */
    private function getNextPriorityLevel(AppealPriority $current): AppealPriority
    {
        return match($current) {
            AppealPriority::LOW => AppealPriority::MEDIUM,
            AppealPriority::MEDIUM => AppealPriority::HIGH,
            AppealPriority::HIGH => AppealPriority::CRITICAL,
            AppealPriority::CRITICAL => AppealPriority::CRITICAL, // Cannot escalate further
        };
    }

    /**
     * Bulk operations for admin efficiency
     */
    public function bulkAssignAppeals(array $appealIds, Admin $admin): int
    {
        $count = ElectionAppeal::whereIn('id', $appealIds)
            ->whereNull('assigned_to')
            ->update([
                'assigned_to' => $admin->id,
                'assigned_at' => now(),
                'status' => AppealStatus::UNDER_REVIEW,
            ]);

        if ($count > 0) {
            $this->auditLog->log(
                'appeals_bulk_assigned',
                $admin->id,
                'election_appeals',
                null,
                [
                    'appeal_ids' => $appealIds,
                    'assigned_to' => $admin->id,
                    'count' => $count,
                ]
            );
        }

        return $count;
    }

    /**
     * Get appeal statistics
     */
    public function getStatistics(): array
    {
        $stats = [
            'total' => ElectionAppeal::count(),
            'pending' => ElectionAppeal::where('status', AppealStatus::SUBMITTED)->count(),
            'under_review' => ElectionAppeal::where('status', AppealStatus::UNDER_REVIEW)->count(),
            'resolved' => ElectionAppeal::resolved()->count(),
            'overdue' => ElectionAppeal::overdue()->count(),
            'by_priority' => [
                'critical' => ElectionAppeal::byPriority(AppealPriority::CRITICAL)->count(),
                'high' => ElectionAppeal::byPriority(AppealPriority::HIGH)->count(),
                'medium' => ElectionAppeal::byPriority(AppealPriority::MEDIUM)->count(),
                'low' => ElectionAppeal::byPriority(AppealPriority::LOW)->count(),
            ],
            'by_type' => collect(AppealType::cases())->mapWithKeys(fn($type) => [
                $type->value => ElectionAppeal::where('type', $type)->count()
            ])->toArray(),
        ];

        return $stats;
    }
}