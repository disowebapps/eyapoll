<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Election\Election;

class EligibleVoters extends Component
{
    public $electionId;
    public $election;
    public $voters = [];

    public function mount($electionId)
    {
        if (!auth()->guard('admin')->check()) {
            abort(403, 'Admin access required');
        }
        
        $this->electionId = $electionId;
        $this->election = Election::with('voteTokens.user')->findOrFail($electionId);
        $this->loadVoters();
    }

    public function loadVoters()
    {
        // HYBRID: Get voters from vote tokens (official voter register)
        $this->voters = $this->election->voteTokens->map(function ($token) {
            return [
                'name' => $token->user->name ?? 'Unknown User',
                'email' => $token->user->email ?? 'No Email',
                'token_id' => $token->token_id,
                'has_voted' => $token->is_used,
                'registered_at' => $token->issued_at,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.admin.eligible-voters');
    }
}