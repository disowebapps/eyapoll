<?php

namespace App\Jobs\Elections;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Election\Election;
use App\Services\Election\ElectionArchiveService;
use Exception;

class ArchiveElection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60; // 1 minute delay between retries

    protected Election $election;
    protected bool $force;

    /**
     * Create a new job instance.
     */
    public function __construct(Election $election, bool $force = false)
    {
        $this->election = $election;
        $this->force = $force;
    }

    /**
     * Execute the job.
     */
    public function handle(ElectionArchiveService $archiveService): void
    {
        try {
            Log::info('Starting election archiving job', [
                'election_id' => $this->election->id,
                'election_title' => $this->election->title,
                'force' => $this->force,
                'attempt' => $this->attempts(),
            ]);

            $result = $archiveService->archiveElection($this->election, $this->force);

            if ($result) {
                Log::info('Election archiving job completed successfully', [
                    'election_id' => $this->election->id,
                    'election_title' => $this->election->title,
                ]);
            } else {
                Log::error('Election archiving job failed', [
                    'election_id' => $this->election->id,
                    'election_title' => $this->election->title,
                ]);
                throw new Exception('Archive service returned false');
            }

        } catch (Exception $e) {
            Log::error('Election archiving job failed with exception', [
                'election_id' => $this->election->id,
                'election_title' => $this->election->title,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);

            // Re-throw to trigger retry or failure handling
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::critical('Election archiving job failed permanently', [
            'election_id' => $this->election->id,
            'election_title' => $this->election->title,
            'error' => $exception->getMessage(),
            'max_attempts' => $this->tries,
        ]);

        // Could send admin notification here for permanent failures
    }

    /**
     * Get the queue name for the job.
     */
    public function queue(): string
    {
        return 'election-archiving';
    }
}