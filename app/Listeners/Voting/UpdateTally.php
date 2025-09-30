<?php

namespace App\Listeners\Voting;

use App\Events\Voting\VoteCast;
use App\Models\Voting\VoteTally;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UpdateTally implements ShouldQueue
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
            $voteRecord = $event->voteRecord;
            $authorization = $event->authorization;
            $election = $authorization->election;

            // Decrypt and process vote selections
            $selections = decrypt($voteRecord->encrypted_selections);

            foreach ($selections as $positionId => $candidateIds) {
                if (empty($candidateIds)) {
                    // Handle abstention
                    $this->updateTallyForCandidate($election->id, $positionId, null);
                } else {
                    // Update tally for each selected candidate
                    foreach ($candidateIds as $candidateId) {
                        $this->updateTallyForCandidate($election->id, $positionId, $candidateId);
                    }
                }
            }

            // Clear cached results for this election
            $this->clearElectionCache($election->id);

            Log::info('Vote tally updated successfully', [
                'vote_id' => $vote->id,
                'election_id' => $election->id,
                'position_id' => $position->id,
                'candidate_ids' => $selectedCandidateIds,
                'is_abstention' => empty($selectedCandidateIds),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update vote tally', [
                'vote_id' => $event->vote->id,
                'election_id' => $event->vote->election_id,
                'position_id' => $event->vote->position_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger job retry
            throw $e;
        }
    }

    /**
     * Update tally for a specific candidate (or abstention if candidateId is null)
     */
    private function updateTallyForCandidate(int $electionId, int $positionId, ?int $candidateId): void
    {
        $tally = VoteTally::firstOrCreate([
            'election_id' => $electionId,
            'position_id' => $positionId,
            'candidate_id' => $candidateId,
        ], [
            'vote_count' => 0,
        ]);

        $tally->incrementVoteCount();
    }

    /**
     * Clear cached election data
     */
    private function clearElectionCache(int $electionId): void
    {
        $cacheKeys = [
            "election_results_{$electionId}",
            "election_progress_{$electionId}",
            "election_statistics_{$electionId}",
            "election_turnout_{$electionId}",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear position-specific caches
        $positions = \App\Models\Election\Position::where('election_id', $electionId)->pluck('id');
        
        foreach ($positions as $positionId) {
            Cache::forget("position_results_{$positionId}");
            Cache::forget("position_tallies_{$positionId}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(VoteCast $event, \Throwable $exception): void
    {
        Log::error('Vote tally update job failed permanently', [
            'vote_id' => $event->vote->id,
            'election_id' => $event->vote->election_id,
            'position_id' => $event->vote->position_id,
            'error' => $exception->getMessage(),
        ]);

        // In production, this should trigger an immediate alert
        // as tally accuracy is critical for election integrity
        if (app()->environment('production')) {
            Log::critical('Critical: Vote tally system failure', [
                'vote_id' => $event->vote->id,
                'election_id' => $event->vote->election_id,
                'error' => $exception->getMessage(),
            ]);

            // Could trigger emergency notification to administrators
            try {
                $admins = \App\Models\User::where('role', 'admin')
                    ->where('status', 'approved')
                    ->get();

                foreach ($admins as $admin) {
                    app(\App\Services\Notification\NotificationService::class)->send(
                        $admin,
                        'system_alert',
                        [
                            'alert_type' => 'Vote Tally Failure',
                            'vote_id' => $event->vote->id,
                            'election_id' => $event->vote->election_id,
                            'error' => $exception->getMessage(),
                            'timestamp' => now()->toISOString(),
                        ],
                        'email'
                    );
                }
            } catch (\Exception $alertException) {
                Log::error('Failed to send tally failure alert', [
                    'original_error' => $exception->getMessage(),
                    'alert_error' => $alertException->getMessage(),
                ]);
            }
        }
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // 30 seconds, 1 minute, 2 minutes
    }
}