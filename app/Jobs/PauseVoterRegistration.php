<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Election\Election;
use App\Services\VoterRegistrationService;

class PauseVoterRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Election $election
    ) {}

    public function handle(): void
    {
        try {
            VoterRegistrationService::pause($this->election);
        } catch (\Exception $e) {
            \Log::error('Failed to pause voter registration', [
                'election_id' => $this->election->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}