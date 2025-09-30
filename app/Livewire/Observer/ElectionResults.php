<?php

namespace App\Livewire\Observer;

use Livewire\Component;
use Livewire\Attributes\{Lazy, Computed};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Election\Election;
use App\Models\Voting\VoteRecord;
use App\Models\Voting\VoteTally;

#[Lazy]
class ElectionResults extends Component
{
    public Election $election;
    public $isPolling = true;

    public function mount($electionId)
    {
        $this->election = Election::with(['positions.candidates.user', 'voteTokens'])->findOrFail($electionId);
    }

    #[Computed]
    public function overallStats()
    {
        $cacheKey = "election_stats_{$this->election->id}";
        $cacheTtl = $this->election->status->value === 'active' ? 30 : 3600;
        
        return Cache::remember($cacheKey, $cacheTtl, function () {
            // Get total eligible voters
            $totalEligible = DB::table('vote_tokens')
                ->where('election_id', $this->election->id)
                ->count();

            // Get total votes cast from vote_tallies table
            $totalVotes = DB::table('vote_tallies')
                ->join('positions', 'vote_tallies.position_id', '=', 'positions.id')
                ->where('positions.election_id', $this->election->id)
                ->whereNotNull('vote_tallies.candidate_id')
                ->sum('vote_tallies.vote_count');
            
            return [
                'total_eligible' => $totalEligible,
                'total_votes' => $totalVotes,
                'turnout_percentage' => $totalEligible > 0 ? round(($totalVotes / $totalEligible) * 100, 1) : 0,
                'positions_count' => $this->election->positions->count(),
            ];
        });
    }

    #[Computed]
    public function positions()
    {
        $cacheKey = "election_results_{$this->election->id}";
        $cacheTtl = $this->election->status->value === 'active' ? 30 : 3600;

        return Cache::remember($cacheKey, $cacheTtl, function () {
            return DB::table('positions')
                ->select('positions.id', 'positions.title', 'positions.description')
                ->where('positions.election_id', $this->election->id)
                ->orderBy('positions.order_index')
                ->get()
                ->map(function ($position) {
                    $results = DB::table('vote_tallies')
                        ->join('candidates', 'vote_tallies.candidate_id', '=', 'candidates.id')
                        ->join('users', 'candidates.user_id', '=', 'users.id')
                        ->select(
                            'vote_tallies.candidate_id',
                            DB::raw('CONCAT(users.first_name, " ", users.last_name) as candidate_name'),
                            'vote_tallies.vote_count'
                        )
                        ->where('vote_tallies.position_id', $position->id)
                        ->orderBy('vote_tallies.vote_count', 'desc')
                        ->get();

                    $totalVotes = $results->sum('vote_count');

                    $formattedResults = $results->map(function ($result, $index) use ($totalVotes) {
                        return [
                            'candidate_id' => $result->candidate_id,
                            'candidate_name' => $result->candidate_name,
                            'vote_count' => $result->vote_count,
                            'percentage' => $totalVotes > 0 ? round(($result->vote_count / $totalVotes) * 100, 2) : 0,
                            'ranking' => $index + 1,
                            'is_leading' => $index === 0 && $totalVotes > 0,
                        ];
                    });

                    // Get abstentions count
                    $abstentions = DB::table('vote_tallies')
                        ->where('position_id', $position->id)
                        ->whereNull('candidate_id')
                        ->sum('vote_count');

                    return [
                        'id' => $position->id,
                        'title' => $position->title,
                        'description' => $position->description,
                        'total_votes' => $totalVotes,
                        'results' => $formattedResults->toArray(),
                        'abstentions' => $abstentions ?? 0,
                    ];
                });
        });
    }

    public function refreshResults()
    {
        Cache::forget("election_stats_{$this->election->id}");
        Cache::forget("election_results_{$this->election->id}");
        $this->dispatch('$refresh');
    }

    public function togglePolling()
    {
        $this->isPolling = !$this->isPolling;
    }

    public function exportResults($format = 'csv')
    {
        return redirect()->route('observer.election-results.export', [
            'election' => $this->election->id,
            'format' => $format
        ]);
    }

    public function viewVoterRegister()
    {
        return redirect()->route('observer.voter-register', $this->election->id);
    }

    public function render()
    {
        return view('livewire.observer.election-results');
    }
}
