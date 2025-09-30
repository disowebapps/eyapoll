<?php

namespace App\Livewire\Observer;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Election\Election;
use Illuminate\Support\Facades\Cache;

class Elections extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';

    public function mount()
    {
        if (request()->has('status')) {
            $this->statusFilter = request('status');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function viewElectionResults($electionId)
    {
        return redirect()->route('observer.election-results', $electionId);
    }

    public function viewElectionPositions($electionId)
    {
        return redirect()->route('observer.election-positions', $electionId);
    }

    public function viewElectionDetails($electionId)
    {
        return redirect()->route('observer.election-details', $electionId);
    }

    #[Computed]
    public function elections()
    {
        $cacheKey = "observer_elections_{$this->search}_{$this->statusFilter}_" . $this->getPage();
        
        return Cache::remember($cacheKey, 60, function () {
            return Election::select(['id', 'title', 'status', 'starts_at', 'ends_at'])
                ->withCount(['positions', 'voteTokens'])
                ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
                ->orderByRaw("CASE 
                    WHEN status = 'active' THEN 1 
                    WHEN status = 'scheduled' THEN 2 
                    WHEN status = 'ended' THEN 3 
                    ELSE 4 END")
                ->orderBy('starts_at', 'desc')
                ->paginate(12)
                ->through(function ($election) {
                    $totalTokens = $election->vote_tokens_count;
                    $usedTokens = Cache::remember("used_tokens_{$election->id}", 300, 
                        fn() => $election->voteTokens()->where('is_used', true)->count()
                    );
                    $election->votes_count = Cache::remember("votes_count_{$election->id}", 300,
                        fn() => \App\Models\Voting\VoteRecord::where('election_id', $election->id)->count()
                    );
                    $election->voter_turnout = $totalTokens > 0 ? round(($usedTokens / $totalTokens) * 100) : 0;
                    return $election;
                });
        });
    }

    public function render()
    {
        return view('livewire.observer.elections');
    }
}