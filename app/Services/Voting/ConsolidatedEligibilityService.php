<?php

namespace App\Services\Voting;

use App\Models\User;
use App\Models\Election\Election;
use App\Enums\Auth\UserStatus;
use Illuminate\Support\Facades\Log;

class ConsolidatedEligibilityService
{
    /**
     * Comprehensive eligibility check for voting
     */
    public function checkEligibility(User $user, Election $election): array
    {
        $reasons = [];
        
        // 1. User must be ACCREDITED
        if ($user->status !== UserStatus::ACCREDITED) {
            $reasons[] = 'User is not accredited to vote';
        }
        
        // 2. Election must be active
        if (!$election->canAcceptVotes()) {
            $reasons[] = 'Election is not accepting votes';
        }
        
        // 3. Must have unused vote token
        $tokenQuery = $user->voteTokens()
            ->where('election_id', $election->id)
            ->where('is_used', false);
            
        $hasToken = $tokenQuery->exists();
        
        Log::info('Vote token check', [
            'user_id' => $user->id,
            'election_id' => $election->id,
            'has_token' => $hasToken,
            'total_tokens' => $user->voteTokens()->where('election_id', $election->id)->count(),
            'unused_tokens' => $user->voteTokens()->where('election_id', $election->id)->where('is_used', false)->count(),
            'used_tokens' => $user->voteTokens()->where('election_id', $election->id)->where('is_used', true)->count()
        ]);
            
        if (!$hasToken) {
            $reasons[] = 'No valid vote token for this election';
        }
        
        // 4. Must not have already voted
        if ($user->hasVotedInElection($election)) {
            $reasons[] = 'Already voted in this election';
        }
        
        return [
            'eligible' => empty($reasons),
            'reasons' => $reasons
        ];
    }
    
    /**
     * Get UI button state for voting
     */
    public function getVotingButtonState(User $user, Election $election): array
    {
        $eligibility = $this->checkEligibility($user, $election);
        
        if ($eligibility['eligible']) {
            return [
                'can_vote' => true,
                'button_class' => 'bg-blue-600 hover:bg-blue-700 text-white',
                'button_text' => 'Vote Now',
                'icon' => 'vote'
            ];
        }
        
        // Determine specific reason for better UX
        $reasons = $eligibility['reasons'];
        
        if (in_array('Already voted in this election', $reasons)) {
            return [
                'can_vote' => false,
                'button_class' => 'bg-gray-400 text-gray-600 cursor-not-allowed',
                'button_text' => 'Already Voted',
                'icon' => 'check'
            ];
        }
        
        if (in_array('User is not accredited to vote', $reasons)) {
            return [
                'can_vote' => false,
                'button_class' => 'bg-gray-400 text-gray-600 cursor-not-allowed',
                'button_text' => 'You are not accredited to vote',
                'icon' => 'warning'
            ];
        }
        
        return [
            'can_vote' => false,
            'button_class' => 'bg-gray-400 text-gray-600 cursor-not-allowed',
            'button_text' => 'Cannot vote',
            'icon' => 'warning'
        ];
    }
}