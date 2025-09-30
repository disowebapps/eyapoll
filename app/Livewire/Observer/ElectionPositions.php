<?php

namespace App\Livewire\Observer;

use Livewire\Component;
use App\Models\Election\Election;

class ElectionPositions extends Component
{
    public $electionId;
    public $election;

    public function mount($electionId)
    {
        $this->electionId = $electionId;
        $this->loadElection();
    }

    public function loadElection()
    {
        $this->election = Election::with([
            'positions.candidates.user',
            'positions.candidates.approver',
            'positions.candidates.suspender',
            'positions.voteTallies',
            'voteTokens'
        ])->findOrFail($this->electionId);
    }

    public function getVoterTurnout()
    {
        $totalTokens = $this->election->voteTokens()->count();
        $usedTokens = $this->election->voteTokens()->where('is_used', true)->count();
        
        return [
            'total' => $totalTokens,
            'voted' => $usedTokens,
            'percentage' => $totalTokens > 0 ? round(($usedTokens / $totalTokens) * 100, 1) : 0
        ];
    }

    public function backToElections()
    {
        return redirect()->route('observer.elections');
    }

    public function render()
    {
        return view('livewire.observer.election-positions', [
            'voterTurnout' => $this->getVoterTurnout()
        ])->layout('layouts.observer-app');
    }
}