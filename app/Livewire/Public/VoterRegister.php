<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Election\Election;
use App\Models\Voting\VoteToken;

class VoterRegister extends Component
{

    public $elections = [];
    public $selectedElection = null;
    public $verifiedVoters = [];
    public $search = '';
    public $allVoters = [];

    public function mount()
    {
        $this->elections = Election::whereNotNull('voter_register_published')
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
        
        $tokens = VoteToken::where('election_id', $this->selectedElection)
            ->with('user:id,first_name,last_name,email')
            ->orderBy('created_at')
            ->get();
        
        $this->allVoters = $tokens->map(fn($token) => [
            'name' => $token->user->first_name . ' ' . $token->user->last_name,
            'email' => $token->user->email,
            'status' => $token->is_used ? 'Voted' : 'Accredited',
            'registered_at' => $token->created_at->format('M d, Y')
        ])->toArray();
        
        $this->filterVoters();
    }
    
    public function updatedSearch()
    {
        $this->filterVoters();
    }
    
    public function filterVoters()
    {
        if (empty($this->search)) {
            $this->verifiedVoters = $this->allVoters;
            return;
        }
        
        $search = strtolower($this->search);
        \Log::info('Searching for: ' . $search);
        \Log::info('Total voters to search: ' . count($this->allVoters));
        
        $this->verifiedVoters = array_filter($this->allVoters, function($voter) use ($search) {
            $nameMatch = str_contains(strtolower($voter['name']), $search);
            $emailMatch = str_contains(strtolower($voter['email']), $search);
            return $nameMatch || $emailMatch;
        });
        
        \Log::info('Filtered results: ' . count($this->verifiedVoters));
    }



    public function render()
    {
        return view('livewire.public.voter-register');
    }
}