<?php

namespace App\Livewire\Voter;

use Livewire\Component;
use App\Services\VoterStatsService;
use Illuminate\Support\Facades\Auth;

class VotingHistory extends Component
{
    public function render()
    {
        $user = Auth::user();
        $voterStatsService = app(VoterStatsService::class);
        
        $voteRecords = $voterStatsService->getVoteRecords($user);
        
        return view('livewire.voter.voting-history', [
            'voteRecords' => $voteRecords
        ]);
    }
}