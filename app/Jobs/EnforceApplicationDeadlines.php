<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Candidate\Candidate;
use App\Models\Election\Election;

class EnforceApplicationDeadlines implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle(): void
    {
        Log::info('Starting application deadline enforcement job');

        // Find elections that have passed their application deadline
        $expiredElections = Election::where('application_deadline', '<', now())
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'completed')
            ->get();

        $applicationsRejected = 0;
        $applicationsClosed = 0;

        foreach ($expiredElections as $election) {
            // Reject all pending applications that haven't paid
            $unpaidApplications = Candidate::where('election_id', $election->id)
                ->where('status', 'pending')
                ->where('payment_status', '!=', 'paid')
                ->where('payment_status', '!=', 'waived')
                ->get();

            foreach ($unpaidApplications as $application) {
                $application->update([
                    'status' => 'rejected',
                    'rejection_reason' => 'Application deadline passed without payment',
                ]);

                Log::info('Application auto-rejected due to deadline', [
                    'candidate_id' => $application->id,
                    'election_id' => $election->id,
                    'reason' => 'Payment not received by deadline',
                ]);

                $applicationsRejected++;
            }

            // Close the election's application period
            $election->update(['status' => \App\Enums\Election\ElectionStatus::UPCOMING->value]);

            Log::info('Election application period closed', [
                'election_id' => $election->id,
                'applications_rejected' => $unpaidApplications->count(),
            ]);

            $applicationsClosed++;
        }

        Log::info('Application deadline enforcement completed', [
            'elections_processed' => $expiredElections->count(),
            'applications_rejected' => $applicationsRejected,
            'application_periods_closed' => $applicationsClosed,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Application deadline enforcement job failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}