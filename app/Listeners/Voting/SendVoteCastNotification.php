<?php

namespace App\Listeners\Voting;

use App\Events\Voting\VoteCast;
use App\Services\Notification\NotificationService;
use App\Enums\Notification\NotificationEventType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendVoteCastNotification implements ShouldQueue
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
    public function handle(VoteCast $event): void
    {
        try {
            $vote = $event->vote;
            $voter = $event->voter;

            // Prepare notification data
            $data = [
                'election_title' => $vote->election->title,
                'position_title' => $vote->position->title,
                'candidate_name' => $vote->candidate->user->full_name,
                'receipt_hash' => $vote->receipt_hash,
                'cast_at' => $vote->created_at->format('M j, Y g:i A'),
                'verification_url' => route('public.verify-receipt', ['hash' => $vote->receipt_hash]),
            ];

            // Send in-app notification to the voter
            $this->notificationService->sendByEvent(
                NotificationEventType::VOTE_CAST,
                $voter,
                $data
            );

            Log::info('Vote cast notification sent', [
                'vote_id' => $vote->id,
                'voter_id' => $voter->id,
                'election_id' => $vote->election->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send vote cast notification', [
                'vote_id' => $event->vote->id ?? null,
                'voter_id' => $event->voter->id ?? null,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(VoteCast $event, \Throwable $exception): void
    {
        Log::critical('Vote cast notification failed permanently', [
            'vote_id' => $event->vote->id ?? null,
            'voter_id' => $event->voter->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}