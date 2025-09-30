<?php

namespace App\Listeners\Voting;

use App\Events\Voting\VoteCast;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateReceipt implements ShouldQueue
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
            $voteToken = $event->voteToken;

            // Send vote receipt notification via email
            $this->notificationService->send(
                $voter,
                'vote_receipt',
                [
                    'user_name' => $voter->full_name,
                    'election_title' => $vote->election->title,
                    'position_title' => $vote->position->title,
                    'receipt_hash' => $vote->receipt_hash,
                    'verification_url' => route('verify.receipt', ['hash' => $vote->receipt_hash]),
                    'cast_at' => $vote->cast_at->format('M j, Y \a\t g:i A'),
                    'platform_name' => config('ayapoll.platform_name', 'AYApoll'),
                ],
                'email'
            );

            // Send in-app notification
            if (config('ayapoll.notification_channels.in_app', true)) {
                $this->notificationService->send(
                    $voter,
                    'vote_cast',
                    [
                        'user_name' => $voter->full_name,
                        'election_title' => $vote->election->title,
                        'position_title' => $vote->position->title,
                        'receipt_hash' => substr($vote->receipt_hash, 0, 8) . '...' . substr($vote->receipt_hash, -8),
                        'cast_at' => $vote->cast_at->format('M j, Y \a\t g:i A'),
                        'verification_url' => route('verify.receipt', ['hash' => $vote->receipt_hash]),
                    ],
                    'in_app'
                );
            }

            Log::info('Vote receipt generated and sent', [
                'vote_id' => $vote->id,
                'voter_id' => $voter->id,
                'election_id' => $vote->election_id,
                'position_id' => $vote->position_id,
                'receipt_hash' => $vote->receipt_hash,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate vote receipt', [
                'vote_id' => $event->vote->id,
                'voter_id' => $event->voter->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't re-throw as receipt generation failure shouldn't break voting
            // But we should alert administrators
            if (app()->environment('production')) {
                Log::critical('Critical: Vote receipt generation failure', [
                    'vote_id' => $event->vote->id,
                    'voter_id' => $event->voter->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(VoteCast $event, \Throwable $exception): void
    {
        Log::error('Vote receipt generation job failed permanently', [
            'vote_id' => $event->vote->id,
            'voter_id' => $event->voter->id,
            'error' => $exception->getMessage(),
        ]);

        // In production, this should trigger an immediate alert
        if (app()->environment('production')) {
            Log::critical('Critical: Vote receipt system failure', [
                'vote_id' => $event->vote->id,
                'voter_id' => $event->voter->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}