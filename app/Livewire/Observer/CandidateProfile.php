<?php

namespace App\Livewire\Observer;

use Livewire\Component;
use App\Models\Candidate\Candidate;

class CandidateProfile extends Component
{
    public $candidateId;
    public $candidate;

    public function mount($candidateId)
    {
        $this->candidateId = $candidateId;
        $this->candidate = Candidate::with(['user', 'position'])->findOrFail($candidateId);
    }

    public function render()
    {
        return view('livewire.observer.candidate-profile');
    }
}