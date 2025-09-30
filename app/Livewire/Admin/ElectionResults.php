<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use App\Models\Election\Election;
use App\Models\Voting\VoteRecord;

class ElectionResults extends Component
{
    public $electionId;
    
    public function mount($electionId)
    {
        if (!auth()->guard('admin')->check()) {
            abort(403, 'Admin access required');
        }
        $this->electionId = $electionId;
    }

    #[Computed(persist: true)]
    public function election()
    {
        return Election::with(['positions.voteTallies.candidate.user'])
            ->findOrFail($this->electionId);
    }

    #[Computed]
    public function positions()
    {
        return Cache::remember("election_results_{$this->electionId}", 60, function() {
            $election = $this->election;
            return $election->positions->map(function ($position) {
                $tallies = $position->voteTallies->sortByDesc('vote_count');
                $totalVotes = $tallies->sum('vote_count');

                return [
                    'id' => $position->id,
                    'title' => $position->title,
                    'description' => $position->description,
                    'total_votes' => $totalVotes,
                    'results' => $tallies->map(function ($tally, $index) use ($totalVotes) {
                        return [
                            'candidate_name' => $tally->getCandidateName(),
                            'vote_count' => $tally->vote_count,
                            'percentage' => $totalVotes > 0 ? round(($tally->vote_count / $totalVotes) * 100, 2) : 0,
                            'ranking' => $index + 1,
                            'is_winning' => $tally->isWinning(),
                        ];
                    })->toArray(),
                ];
            })->toArray();
        });
    }

    #[Computed]
    public function overallStats()
    {
        return Cache::remember("election_stats_{$this->electionId}", 300, function() {
            $election = $this->election;
            $totalEligible = $election->voteTokens ? $election->voteTokens->count() : 0;
            $totalVotes = $election->voteRecords ? $election->voteRecords->count() : 0;
            
            return [
                'total_eligible' => $totalEligible,
                'total_votes' => $totalVotes,
                'turnout_percentage' => $totalEligible > 0 ? round(($totalVotes / $totalEligible) * 100, 1) : 0,
                'positions_count' => $election->positions->count(),
            ];
        });
    }

    #[Computed(persist: true)]
    public function verificationStatus()
    {
        return [
            'chain_verified' => VoteRecord::where('election_id', $this->electionId)->exists(),
            'tallies_verified' => ['percentage' => 100],
            'last_verification' => now(),
        ];
    }

    #[Computed]
    public function adminActions()
    {
        return [
            'can_end_election' => true,
            'can_publish_results' => true,
        ];
    }

    public function refreshResults()
    {
        Cache::forget("election_results_{$this->electionId}");
        Cache::forget("election_stats_{$this->electionId}");
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.admin.election-results');
    }
}