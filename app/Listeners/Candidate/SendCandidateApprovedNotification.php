<?php

namespace App\Listeners\Candidate;

use App\Events\CandidateApproved;
use App\Services\Notification\NotificationService;
use App\Enums\Notification\NotificationEventType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendCandidateApprovedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(CandidateApproved $event): void
    {
        try {
            $candidate = $event->candidate;

            // Prepare notification data
            $data = [
                'candidate_name' => $candidate->user->full_name,
                'election_title' => $candidate->election->title,
                'position_title' => $candidate->position->title,
                'approval_date' => now()->format('M j, Y g:i A'),
                'election_url' => route('voter.dashboard'),
            ];

            // Send notification to the approved candidate
            $this->notificationService->sendByEvent(
                NotificationEventType::CANDIDATE_APPROVED,
                $candidate->user,
                $data
            );

            Log::info('Candidate approved notification sent', [
                'candidate_id' => $candidate->id,
                'user_id' => $candidate->user->id,
                'election_id' => $candidate->election->id,
                'position_id' => $candidate->position->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send candidate approved notification', [
                'candidate_id' => $event->candidate->id ?? null,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(CandidateApproved $event, \Throwable $exception): void
    {
        Log::critical('Candidate approved notification failed permanently', [
            'candidate_id' => $event->candidate->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}