<?php

namespace App\Listeners\Voting;

use App\Events\Voting\VoteCast;
use App\Models\Voting\VoteTally;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ClearResultCache implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VoteCast $event): void
    {
        $vote = $event->vote;

        // Clear cache for the election this vote belongs to
        VoteTally::clearElectionCache($vote->election_id);

        // Also clear position-specific cache
        VoteTally::clearPositionCache($vote->position_id);
    }

    /**
     * Handle a job failure.
     */
    public function failed(VoteCast $event, \Throwable $exception): void
    {
        // Log the failure but don't throw - cache clearing is not critical
        Log::warning('Failed to clear result cache after vote cast', [
            'election_id' => $event->vote->election_id,
            'position_id' => $event->vote->position_id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
