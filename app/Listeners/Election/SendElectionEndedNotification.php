<?php

namespace App\Listeners\Election;

use App\Events\Election\ElectionEnded;
use App\Services\Notification\NotificationService;
use App\Enums\Notification\NotificationEventType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendElectionEndedNotification implements ShouldQueue
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
    public function handle(ElectionEnded $event): void
    {
        try {
            $election = $event->election;
            $endedBy = $event->endedBy;

            // Get all eligible voters for this election
            $eligibleVoters = $election->getEligibleVoters();

            // Prepare notification data
            $data = [
                'election_title' => $election->title,
                'election_description' => $election->description,
                'election_type' => $election->type->label(),
                'started_at' => $election->starts_at->format('M j, Y g:i A'),
                'ended_at' => $election->ends_at->format('M j, Y g:i A'),
                'results_url' => route('voter.results', $election->id),
                'ended_by' => $endedBy?->full_name ?? 'System',
            ];

            $notificationsSent = 0;

            // Send notification to each eligible voter
            foreach ($eligibleVoters as $voter) {
                try {
                    $this->notificationService->sendByEvent(
                        NotificationEventType::ELECTION_ENDED,
                        $voter,
                        $data
                    );
                    $notificationsSent++;
                } catch (\Exception $e) {
                    Log::warning('Failed to send election ended notification to voter', [
                        'election_id' => $election->id,
                        'voter_id' => $voter->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Election ended notifications sent', [
                'election_id' => $election->id,
                'total_voters' => $eligibleVoters->count(),
                'notifications_sent' => $notificationsSent,
                'ended_by' => $endedBy?->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send election ended notifications', [
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
    public function failed(ElectionEnded $event, \Throwable $exception): void
    {
        Log::critical('Election ended notifications failed permanently', [
            'election_id' => $event->election->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}