<?php

namespace App\Services\Voting;

use App\Models\User;
use App\Models\Election\Election;
use Illuminate\Support\Facades\Log;

class EligibilityService
{
    public function __construct(
        private ConsolidatedEligibilityService $consolidatedService
    ) {}
    
    public function checkEligibility(User $user, Election $election)
    {
        Log::info('Eligibility check initiated', [
            'user_id' => $user->id,
            'election_id' => $election->id
        ]);
        
        $result = $this->consolidatedService->checkEligibility($user, $election);
        
        Log::info('Eligibility check completed', [
            'user_id' => $user->id,
            'election_id' => $election->id,
            'eligible' => $result['eligible'],
            'violations' => count($result['reasons'])
        ]);
        
        return new class($result) {
            private $result;
            
            public function __construct($result) {
                $this->result = $result;
            }
            
            public function isEligible(): bool {
                return $this->result['eligible'];
            }
            
            public function getReasons(): array {
                return $this->result['reasons'];
            }
            
            public function toArray(): array {
                return [
                    'eligible' => $this->result['eligible'],
                    'reasons' => $this->result['reasons'],
                    'checked_at' => now()->toISOString()
                ];
            }
        };
    }
}