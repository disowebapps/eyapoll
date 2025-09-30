<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Candidate\Candidate;
use App\Services\Candidate\CandidateNotificationService;

class SendPaymentReminders implements ShouldQueue
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
        Log::info('Starting payment reminder job');

        $notificationService = app(CandidateNotificationService::class);

        // Find candidates with pending payments who haven't been reminded in the last 24 hours
        $candidatesNeedingReminders = Candidate::where('payment_status', 'pending')
            ->where('application_fee', '>', 0)
            ->where('status', 'pending')
            ->whereHas('election', function ($query) {
                // Only for elections that are still accepting applications
                $query->where('application_deadline', '>', now())
                      ->where('status', '!=', 'cancelled');
            })
            ->whereDoesntHave('paymentHistory', function ($query) {
                // Exclude candidates who have been reminded in the last 24 hours
                $query->where('created_at', '>', now()->subHours(24));
            })
            ->get();

        $remindersSent = 0;

        foreach ($candidatesNeedingReminders as $candidate) {
            try {
                $notificationService->notifyPaymentRequired($candidate);
                $remindersSent++;

                Log::info('Payment reminder sent', [
                    'candidate_id' => $candidate->id,
                    'user_id' => $candidate->user_id,
                    'election_id' => $candidate->election_id,
                    'amount' => $candidate->application_fee,
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send payment reminder', [
                    'candidate_id' => $candidate->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Payment reminder job completed', [
            'candidates_found' => $candidatesNeedingReminders->count(),
            'reminders_sent' => $remindersSent,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Payment reminder job failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}