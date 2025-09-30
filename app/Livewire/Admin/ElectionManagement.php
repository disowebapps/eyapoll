<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Election\Election;
use App\Services\Election\ElectionPhaseManager;
use App\Enums\Election\ElectionPhase;

class ElectionManagement extends Component
{
    public Election $election;
    public $showPhaseTransition = false;
    public $targetPhase;

    public function mount($electionId)
    {
        $this->election = Election::with(['positions', 'candidates', 'voteTokens'])->findOrFail($electionId);
    }

    public function refreshElection()
    {
        $this->election->refresh();
    }

    public function transitionPhase($phase)
    {
        $phaseManager = app(ElectionPhaseManager::class);
        $success = $phaseManager->transitionToPhase(
            $this->election, 
            ElectionPhase::from($phase), 
            auth('admin')->user()
        );

        if ($success) {
            $this->election->refresh();
            $this->dispatch('$refresh');
            session()->flash('success', 'Election phase updated successfully');
        } else {
            session()->flash('error', 'Failed to update election phase');
        }
    }

    public function getAvailableTransitions()
    {
        $currentPhase = $this->election->phase ?? ElectionPhase::SETUP;
        $available = [];

        foreach (ElectionPhase::cases() as $phase) {
            if ($currentPhase->canTransitionTo($phase)) {
                $available[] = $phase;
            }
        }

        return $available;
    }

    public function getEligibleVotersCount()
    {
        // Count approved users (candidates are already included as they must be approved users)
        return \App\Models\User::where('status', 'approved')->count();
    }

    public function render()
    {
        return view('livewire.admin.election-management', [
            'availableTransitions' => $this->getAvailableTransitions(),
            'eligibleVotersCount' => $this->getEligibleVotersCount()
        ]);
    }
}