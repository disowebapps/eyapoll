<?php

namespace App\Services\Voting;

use Illuminate\Support\Facades\Cache;

class BallotDraftService
{
    public function saveDraft(string $voterHash, int $electionId, array $selections): void
    {
        $key = $this->getDraftKey($voterHash, $electionId);
        
        Cache::put($key, encrypt([
            'selections' => $selections,
            'saved_at' => now(),
            'voter_hash' => $voterHash
        ]), now()->addHours(2));
    }

    public function restoreDraft(string $voterHash, int $electionId): ?array
    {
        $key = $this->getDraftKey($voterHash, $electionId);
        $draft = Cache::get($key);

        if ($draft) {
            $data = decrypt($draft);
            return $data['selections'];
        }

        return null;
    }

    public function clearDraft(string $voterHash, int $electionId): void
    {
        $key = $this->getDraftKey($voterHash, $electionId);
        Cache::forget($key);
    }

    private function getDraftKey(string $voterHash, int $electionId): string
    {
        return "ballot_draft_{$voterHash}_{$electionId}";
    }
}