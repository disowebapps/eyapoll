<?php

namespace App\Livewire\Observer;

use Livewire\Component;
use App\Models\Election\Election;

class ElectionDetails extends Component
{
    public Election $election;

    public function mount(Election $election)
    {
        $this->election = $election->load(['positions.candidates.user']);
    }

    public function viewCandidateProfile($candidateId)
    {
        return redirect()->route('observer.candidate-profile', $candidateId);
    }

    public function viewPositionDetails($positionId)
    {
        return redirect()->route('observer.position-details', $positionId);
    }

    public function render()
    {
        return view('livewire.observer.election-details');
    }
}