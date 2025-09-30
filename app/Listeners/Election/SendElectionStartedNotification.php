<?php

namespace App\Listeners\Election;

use App\Events\Election\ElectionStarted;
use App\Services\Notification\NotificationService;
use App\Enums\Notification\NotificationEventType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendElectionStartedNotification implements ShouldQueue
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
    public function handle(ElectionStarted $event): void
    {
        try {
            $election = $event->election;
            $startedBy = $event->startedBy;

            // Get all eligible voters for this election
            $eligibleVoters = $election->getEligibleVoters();

            // Prepare notification data
            $data = [
                'election_title' => $election->title,
                'election_description' => $election->description,
                'election_type' => $election->type->label(),
                'starts_at' => $election->starts_at->format('M j, Y g:i A'),
                'ends_at' => $election->ends_at->format('M j, Y g:i A'),
                'time_remaining' => $election->getTimeRemaining(),
                'positions_count' => $election->positions->count(),
                'voting_url' => route('voter.dashboard'),
                'started_by' => $startedBy?->full_name ?? 'System',
            ];

            $notificationsSent = 0;

            // Send notification to each eligible voter
            foreach ($eligibleVoters as $voter) {
                try {
                    $this->notificationService->sendByEvent(
                        NotificationEventType::ELECTION_STARTED,
                        $voter,
                        $data
                    );
                    $notificationsSent++;
                } catch (\Exception $e) {
                    Log::warning('Failed to send election started notification to voter', [
                        'election_id' => $election->id,
                        'voter_id' => $voter->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Election started notifications sent', [
                'election_id' => $election->id,
                'total_voters' => $eligibleVoters->count(),
                'notifications_sent' => $notificationsSent,
                'started_by' => $startedBy?->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send election started notifications', [
                'election_id' => $event->election->id ?? null,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(ElectionStarted $event, \Throwable $exception): void
    {
        Log::critical('Election started notifications failed permanently', [
            'election_id' => $event->election->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}