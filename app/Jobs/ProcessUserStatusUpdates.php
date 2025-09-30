<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\Auth\UserStatusService;
use Illuminate\Support\Facades\Log;

class ProcessUserStatusUpdates implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 300; // 5 minutes timeout

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(UserStatusService $userStatusService): void
    {
        try {
            Log::info('Starting user status update job');

            $updatedCount = $userStatusService->processAutomaticUpdates();

            Log::info('User status update job completed', [
                'users_updated' => $updatedCount,
            ]);

        } catch (\Exception $e) {
            Log::error('User status update job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('User status update job permanently failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}