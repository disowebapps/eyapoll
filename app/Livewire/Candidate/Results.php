<?php

namespace App\Livewire\Candidate;

use Livewire\Component;
use App\Models\Candidate\Candidate;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Voting\VoteTally;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Results extends Component
{
    use AuthorizesRequests;

    public $election;
    public $candidates = [];
    public $positions = [];
    public $overallStats = [];
    public $lastUpdated;
    public $isPolling = true;

    protected $listeners = ['refreshResults' => 'loadResults'];

    public function mount($electionId = null)
    {
        if ($electionId) {
            $this->election = Election::with(['positions.candidates.user'])->findOrFail($electionId);
        } else {
            // Default to latest election with results
            $this->election = Election::where('results_published', true)
                ->orderBy('ended_at', 'desc')
                ->first();

            if (!$this->election) {
                abort(404, 'No election results available.');
            }
        }

        $this->authorize('viewResults', $this->election);
        $this->loadResults();
    }

    public function loadResults()
    {
        // Only show results if published
        if (!$this->election->results_published) {
            $this->positions = [];
            $this->overallStats = ['message' => 'Results not yet published'];
            return;
        }

        $cacheDuration = $this->election->isActive() ? 30 : 300; // 30s for active, 5min for completed

        $this->positions = Cache::remember(
            "election_positions_results_{$this->election->id}",
            $cacheDuration,
            fn() => $this->election->positions()
                ->with(['voteTallies.candidate.user'])
                ->orderBy('order_index')
                ->get()
                ->map(function ($position) {
                    $tallies = $position->voteTallies()
                        ->with('candidate.user')
                        ->orderBy('vote_count', 'desc')
                        ->get();

                    $totalVotes = $tallies->sum('vote_count');

                    return [
                        'id' => $position->id,
                        'title' => $position->title,
                        'description' => $position->description,
                        'total_votes' => $totalVotes,
                        'total_candidates' => $position->approvedCandidates->count(),
                        'results' => $tallies->map(function ($tally) use ($totalVotes) {
                            $candidate = $tally->candidate;
                            $isCurrentUser = $candidate && $candidate->user_id === Auth::id();

                            return [
                                'candidate_id' => $tally->candidate_id,
                                'candidate_name' => $tally->getCandidateName(),
                                'vote_count' => $tally->vote_count,
                                'percentage' => $totalVotes > 0 ?
                                    round(($tally->vote_count / $totalVotes) * 100, 2) : 0,
                                'ranking' => $tally->getRanking(),
                                'is_winning' => $tally->isWinning(),
                                'is_leading' => $tally->isLeading(),
                                'is_current_user' => $isCurrentUser,
                                'last_updated' => $tally->last_updated,
                            ];
                        })->toArray(),
                        'abstentions' => $tallies->where('candidate_id', null)->sum('vote_count'),
                    ];
                })->toArray()
        );

        $this->overallStats = $this->getOverallStats();
        $this->lastUpdated = now();
    }

    private function getOverallStats()
    {
        return Cache::remember(
            "election_results_stats_{$this->election->id}",
            300,
            function() {
                $totalEligible = $this->election->voteTokens()->count();
                $totalVotes = $this->election->votes()->count();
                $turnoutPercentage = $totalEligible > 0 ? round(($totalVotes / $totalEligible) * 100, 1) : 0;

                // Get current user's candidates
                $userCandidates = Candidate::where('user_id', Auth::id())
                    ->where('election_id', $this->election->id)
                    ->where('status', 'approved')
                    ->get();

                $userTotalVotes = $userCandidates->sum(function ($candidate) {
                    return $candidate->getVoteCount();
                });

                $userPositions = $userCandidates->count();
                $userWins = $userCandidates->filter(function ($candidate) {
                    return $candidate->isWinner();
                })->count();

                return [
                    'total_eligible' => $totalEligible,
                    'total_votes' => $totalVotes,
                    'turnout_percentage' => $turnoutPercentage,
                    'positions_count' => $this->election->positions()->count(),
                    'user_total_votes' => $userTotalVotes,
                    'user_positions' => $userPositions,
                    'user_wins' => $userWins,
                ];
            }
        );
    }

    public function togglePolling()
    {
        $this->isPolling = !$this->isPolling;
    }

    public function refreshResults()
    {
        VoteTally::clearElectionCache($this->election->id);
        $this->loadResults();
    }

    public function viewCandidateDetails($candidateId)
    {
        $candidate = Candidate::findOrFail($candidateId);

        // Only allow viewing details for approved candidates
        if ($candidate->status !== 'approved') {
            session()->flash('error', 'Candidate details not available.');
            return;
        }

        return redirect()->route('candidate.application', $candidate->id);
    }

    public function exportResults()
    {
        // For now, redirect to admin results export
        return redirect()->route('admin.elections.results.export', [
            'election' => $this->election->id,
            'format' => 'pdf'
        ]);
    }

    public function render()
    {
        return view('livewire.candidate.results');
    }
}