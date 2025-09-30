<?php

namespace App\Livewire\Observer;

use Livewire\Component;
use App\Models\Election\Election;
use App\Models\Voting\VoteToken;

class VoterRegister extends Component
{
    public $email = '';
    public $searchResult = null;
    public $elections = [];
    public $selectedElection = null;
    public $verifiedVoters = [];

    public function mount()
    {
        $this->elections = Election::whereNotNull('voter_register_published')
            ->where('phase', '>=', 'verification')
            ->orderBy('starts_at', 'desc')
            ->get();
            
        if ($this->elections->count() > 0) {
            $this->selectedElection = $this->elections->first()->id;
            $this->loadVerifiedVoters();
        }
    }

    public function updatedSelectedElection()
    {
        $this->loadVerifiedVoters();
    }

    public function loadVerifiedVoters()
    {
        if (!$this->selectedElection) return;
        
        $this->verifiedVoters = VoteToken::where('election_id', $this->selectedElection)
            ->with('user')
            ->orderBy('created_at')
            ->get()
            ->map(fn($token) => [
                'name' => $token->user->first_name . ' ' . $token->user->last_name,
                'status' => $token->is_used ? 'Voted' : 'Eligible',
                'registered_at' => $token->created_at->format('M d, Y')
            ]);
    }

    public function checkRegistration()
    {
        $this->validate(['email' => 'required|email']);

        $user = \App\Models\User::where('email', $this->email)->first();
        
        if (!$user) {
            $this->searchResult = ['status' => 'not_found'];
            return;
        }

        $registrations = VoteToken::where('user_id', $user->id)
            ->whereIn('election_id', $this->elections->pluck('id'))
            ->with('election')
            ->get();

        $this->searchResult = [
            'status' => 'found',
            'user' => $user->only(['first_name', 'last_name']),
            'registrations' => $registrations->map(fn($token) => [
                'election_title' => $token->election->title,
                'election_date' => $token->election->starts_at->format('M d, Y'),
                'status' => $token->is_used ? 'voted' : 'eligible'
            ])
        ];
    }

    public function render()
    {
        return view('livewire.observer.voter-register');
    }
}