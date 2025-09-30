<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Candidate\CandidateApplicationDraft;

class SaveApplicationDraft implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $userId,
        private int $electionId,
        private string $cacheKey
    ) {}

    public function handle()
    {
        $data = cache()->get($this->cacheKey);
        
        if (!$data) {
            return; // Data expired or already processed
        }

        CandidateApplicationDraft::updateOrCreate(
            [
                'user_id' => $this->userId,
                'election_id' => $this->electionId,
            ],
            [
                'form_data' => [
                    'selectedPositionId' => $data['selectedPositionId'],
                    'manifesto' => $data['manifesto'],
                    'paymentReference' => $data['paymentReference'],
                    'acceptTerms' => $data['acceptTerms'],
                ],
                'current_step' => $data['current_step'],
            ]
        );

        cache()->forget($this->cacheKey);
    }
}