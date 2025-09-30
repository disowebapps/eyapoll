<?php

namespace App\Listeners\Voting;

use App\Events\Voting\VoteCast;
use App\Services\Audit\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogVote implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(VoteCast $event): void
    {
        try {
            $vote = $event->vote;
            $voter = $event->voter;
            $voteToken = $event->voteToken;

            $auditLogService = app(AuditLogService::class);

            // Log the vote casting action
            // Note: We log the voter context for audit but the vote itself is anonymous
            $auditLogService->log(
                'vote_cast',
                $voter,
                get_class($vote),
                $vote->id,
                null, // No old values for new vote
                [
                    'election_id' => $vote->election_id,
                    'election_title' => $vote->election->title,
                    'position_id' => $vote->position_id,
                    'position_title' => $vote->position->title,
                    'vote_hash' => $vote->vote_hash, // For audit trail only
                    'receipt_hash' => $vote->receipt_hash,
                    'chain_hash' => $vote->chain_hash,
                    'cast_at' => $vote->cast_at->toISOString(),
                    'vote_token_used' => $voteToken->token_hash,
                    'is_abstention' => $vote->isAbstention(),
                    'selection_count' => $vote->getVoteCount(),
                ]
            );

            // Log vote token usage
            $auditLogService->log(
                'vote_token_used',
                $voter,
                get_class($voteToken),
                $voteToken->id,
                ['is_used' => false],
                [
                    'is_used' => true,
                    'used_at' => $voteToken->used_at->toISOString(),
                    'receipt_hash' => $voteToken->vote_receipt_hash,
                ]
            );

            // Log chain integrity verification
            if ($vote->verifyIntegrity()) {
                $auditLogService->logSystemAction(
                    'vote_chain_verified',
                    $vote,
                    [
                        'chain_position' => $this->getVoteChainPosition($vote),
                        'integrity_verified' => true,
                        'verification_timestamp' => now()->toISOString(),
                    ]
                );
            } else {
                $auditLogService->logSystemAction(
                    'vote_chain_integrity_failure',
                    $vote,
                    [
                        'chain_position' => $this->getVoteChainPosition($vote),
                        'integrity_verified' => false,
                        'verification_timestamp' => now()->toISOString(),
                        'alert_level' => 'critical',
                    ]
                );

                // This is a critical security issue
                Log::critical('Vote chain integrity failure detected', [
                    'vote_id' => $vote->id,
                    'election_id' => $vote->election_id,
                    'voter_id' => $voter->id,
                    'chain_hash' => $vote->chain_hash,
                ]);
            }

            Log::info('Vote audit log created successfully', [
                'vote_id' => $vote->id,
                'voter_id' => $voter->id,
                'election_id' => $vote->election_id,
                'position_id' => $vote->position_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create vote audit log', [
                'vote_id' => $event->vote->id,
                'voter_id' => $event->voter->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't re-throw as audit logging failure shouldn't break voting
            // But we should alert administrators
            if (app()->environment('production')) {
                Log::critical('Critical: Vote audit logging failure', [
                    'vote_id' => $event->vote->id,
                    'voter_id' => $event->voter->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get vote position in the chain
     */
    private function getVoteChainPosition($vote): int
    {
        return \App\Models\Voting\VoteRecord::where('election_id', $vote->election_id)
            ->where('cast_at', '<=', $vote->cast_at)
            ->count();
    }

    /**
     * Handle a job failure.
     */
    public function failed(VoteCast $event, \Throwable $exception): void
    {
        Log::error('Vote audit logging job failed permanently', [
            'vote_id' => $event->vote->id,
            'voter_id' => $event->voter->id,
            'error' => $exception->getMessage(),
        ]);

        // In production, this should trigger an alert
        if (app()->environment('production')) {
            Log::critical('Critical: Vote audit system failure', [
                'vote_id' => $event->vote->id,
                'voter_id' => $event->voter->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}