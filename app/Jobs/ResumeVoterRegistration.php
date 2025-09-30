<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Election\Election;
use App\Services\VoterRegistrationService;

class ResumeVoterRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Election $election
    ) {}

    public function handle(): void
    {
        try {
            VoterRegistrationService::resume($this->election);
        } catch (\Exception $e) {
            \Log::error('Failed to resume voter registration', [
                'election_id' => $this->election->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}