<?php

namespace App\Services\Appeal;

use App\Models\ElectionAppeal;
use App\Models\User;
use App\Models\Admin;
use App\Enums\Notification\NotificationEventType;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Collection;

class AppealNotificationService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Notify admins about new appeal submission
     */
    public function notifyAppealSubmitted(ElectionAppeal $appeal): Collection
    {
        $admins = Admin::approved()->get(); // Get approved admins

        $notifications = [];
        foreach ($admins as $admin) {
            $notifications = array_merge(
                $notifications,
                $this->notificationService->sendByEvent(
                    NotificationEventType::APPEAL_SUBMITTED,
                    $admin,
                    [
                        'appeal_id' => $appeal->id,
                        'appeal_uuid' => $appeal->uuid,
                        'appellant_name' => $appeal->appellant->full_name,
                        'election_title' => $appeal->election->title,
                        'appeal_type' => $appeal->type->label(),
                        'appeal_title' => $appeal->title,
                        'priority' => $appeal->priority->label(),
                        'submitted_at' => $appeal->submitted_at->format('Y-m-d H:i:s'),
                        'deadline' => $appeal->deadline_at?->format('Y-m-d H:i:s'),
                        'appeal_url' => route('admin.appeals.show', $appeal->id),
                    ]
                )
            );
        }

        return collect($notifications);
    }

    /**
     * Notify appellant about appeal status update
     */
    public function notifyAppealStatusUpdate(ElectionAppeal $appeal): Collection
    {
        $eventType = match($appeal->status) {
            \App\Enums\Appeal\AppealStatus::UNDER_REVIEW => NotificationEventType::APPEAL_UNDER_REVIEW,
            \App\Enums\Appeal\AppealStatus::APPROVED => NotificationEventType::APPEAL_APPROVED,
            \App\Enums\Appeal\AppealStatus::REJECTED => NotificationEventType::APPEAL_REJECTED,
            \App\Enums\Appeal\AppealStatus::DISMISSED => NotificationEventType::APPEAL_DISMISSED,
            default => null,
        };

        if (!$eventType) {
            return collect();
        }

        return collect($this->notificationService->sendByEvent(
            $eventType,
            $appeal->appellant,
            [
                'appeal_id' => $appeal->id,
                'appeal_uuid' => $appeal->uuid,
                'election_title' => $appeal->election->title,
                'appeal_title' => $appeal->title,
                'status' => $appeal->status->label(),
                'updated_at' => $appeal->reviewed_at?->format('Y-m-d H:i:s'),
                'review_notes' => $appeal->review_notes,
                'resolution' => $appeal->resolution,
                'appeal_url' => route('appeals.show', $appeal->id),
            ]
        ));
    }

    /**
     * Notify appellant about appeal assignment
     */
    public function notifyAppealAssigned(ElectionAppeal $appeal): Collection
    {
        if (!$appeal->assignedTo) {
            return collect();
        }

        return collect($this->notificationService->sendByEvent(
            NotificationEventType::APPEAL_ASSIGNED,
            $appeal->appellant,
            [
                'appeal_id' => $appeal->id,
                'appeal_uuid' => $appeal->uuid,
                'election_title' => $appeal->election->title,
                'appeal_title' => $appeal->title,
                'assigned_to' => $appeal->assignedTo->name ?? 'Administrator',
                'assigned_at' => $appeal->assigned_at?->format('Y-m-d H:i:s'),
                'appeal_url' => route('appeals.show', $appeal->id),
            ]
        ));
    }

    /**
     * Notify appellant about appeal escalation
     */
    public function notifyAppealEscalated(ElectionAppeal $appeal, string $reason): Collection
    {
        return collect($this->notificationService->sendByEvent(
            NotificationEventType::APPEAL_ESCALATED,
            $appeal->appellant,
            [
                'appeal_id' => $appeal->id,
                'appeal_uuid' => $appeal->uuid,
                'election_title' => $appeal->election->title,
                'appeal_title' => $appeal->title,
                'new_priority' => $appeal->priority->label(),
                'escalation_reason' => $reason,
                'escalated_at' => now()->format('Y-m-d H:i:s'),
                'appeal_url' => route('appeals.show', $appeal->id),
            ]
        ));
    }

    /**
     * Notify admins about overdue appeals
     */
    public function notifyOverdueAppeals(Collection $overdueAppeals): Collection
    {
        if ($overdueAppeals->isEmpty()) {
            return collect();
        }

        $admins = Admin::approved()->get(); // Get approved admins
        $notifications = [];

        foreach ($admins as $admin) {
            $notifications = array_merge(
                $notifications,
                $this->notificationService->sendByEvent(
                    NotificationEventType::APPEALS_OVERDUE,
                    $admin,
                    [
                        'overdue_count' => $overdueAppeals->count(),
                        'appeals' => $overdueAppeals->map(fn($appeal) => [
                            'id' => $appeal->id,
                            'title' => $appeal->title,
                            'appellant' => $appeal->appellant->full_name,
                            'days_overdue' => abs($appeal->getDaysUntilDeadline()),
                            'priority' => $appeal->priority->label(),
                        ])->toArray(),
                        'dashboard_url' => route('admin.appeals.index'),
                    ]
                )
            );
        }

        return collect($notifications);
    }

    /**
     * Notify admins about appeals needing escalation
     */
    public function notifyAppealsNeedingEscalation(Collection $escalationAppeals): Collection
    {
        if ($escalationAppeals->isEmpty()) {
            return collect();
        }

        $admins = Admin::approved()->get(); // Get approved admins
        $notifications = [];

        foreach ($admins as $admin) {
            $notifications = array_merge(
                $notifications,
                $this->notificationService->sendByEvent(
                    NotificationEventType::APPEALS_NEED_ESCALATION,
                    $admin,
                    [
                        'escalation_count' => $escalationAppeals->count(),
                        'appeals' => $escalationAppeals->map(fn($appeal) => [
                            'id' => $appeal->id,
                            'title' => $appeal->title,
                            'appellant' => $appeal->appellant->full_name,
                            'assigned_to' => $appeal->assignedTo?->name,
                            'hours_since_assignment' => $appeal->assigned_at ? $appeal->assigned_at->diffInHours(now()) : 0,
                            'priority' => $appeal->priority->label(),
                        ])->toArray(),
                        'dashboard_url' => route('admin.appeals.index'),
                    ]
                )
            );
        }

        return collect($notifications);
    }

    /**
     * Send reminder to appellant about upcoming deadline
     */
    public function sendDeadlineReminder(ElectionAppeal $appeal, int $daysUntilDeadline): Collection
    {
        return collect($this->notificationService->sendByEvent(
            NotificationEventType::APPEAL_DEADLINE_REMINDER,
            $appeal->appellant,
            [
                'appeal_id' => $appeal->id,
                'appeal_uuid' => $appeal->uuid,
                'election_title' => $appeal->election->title,
                'appeal_title' => $appeal->title,
                'days_until_deadline' => $daysUntilDeadline,
                'deadline_date' => $appeal->deadline_at?->format('Y-m-d'),
                'appeal_url' => route('appeals.show', $appeal->id),
            ]
        ));
    }

    /**
     * Notify appellant about document review results
     */
    public function notifyDocumentReview(\App\Models\AppealDocument $document): Collection
    {
        $appeal = $document->appeal;
        $eventType = match($document->status) {
            'approved' => NotificationEventType::APPEAL_DOCUMENT_APPROVED,
            'rejected' => NotificationEventType::APPEAL_DOCUMENT_REJECTED,
            default => null,
        };

        if (!$eventType) {
            return collect();
        }

        return collect($this->notificationService->sendByEvent(
            $eventType,
            $appeal->appellant,
            [
                'appeal_id' => $appeal->id,
                'appeal_uuid' => $appeal->uuid,
                'document_name' => $document->original_filename,
                'review_notes' => $document->review_notes,
                'reviewed_at' => $document->reviewed_at?->format('Y-m-d H:i:s'),
                'appeal_url' => route('appeals.show', $appeal->id),
            ]
        ));
    }
}