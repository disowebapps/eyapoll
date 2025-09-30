<?php

namespace App\Listeners\Voting;

use App\Events\Voting\VoteCast;
use App\Services\Notification\NotificationService;
use App\Enums\Notification\NotificationEventType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendVoteConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    public $delay = 5; // Delay by 5 seconds to ensure vote is processed

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
            $voteToken = $event->voteToken;
            $voter = $event->voter;

            // Prepare notification data
            $data = [
                'election_title' => $vote->election->title,
                'election_type' => $vote->election->type->label(),
                'position_title' => $vote->position->title,
                'vote_hash' => $vote->getShortVoteHash(),
                'receipt_hash' => $vote->getShortReceiptHash(),
                'cast_at' => $vote->cast_at->format('M j, Y g:i A'),
                'verification_url' => $vote->getVerificationUrl(),
                'election_ends_at' => $vote->election->ends_at?->format('M j, Y g:i A'),
            ];

            // Send vote confirmation notification
            $this->notificationService->sendByEvent(
                NotificationEventType::VOTE_CAST,
                $voter,
                $data
            );

            Log::info('Vote confirmation notification sent', [
                'vote_id' => $vote->id,
                'voter_id' => $voter->id,
                'election_id' => $vote->election_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send vote confirmation notification', [
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
        Log::critical('Vote confirmation notification failed permanently', [
            'vote_id' => $event->vote->id ?? null,
            'voter_id' => $event->voter->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}