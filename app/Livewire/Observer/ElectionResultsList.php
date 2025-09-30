<?php

namespace App\Livewire\Observer;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Election\Election;

class ElectionResultsList extends Component
{
    use WithPagination;

    public function viewElectionResults($electionId)
    {
        return redirect()->route('observer.election-results', $electionId);
    }

    public function render()
    {
        $elections = Election::select(['id', 'title', 'status', 'starts_at', 'ends_at'])
            ->whereIn('status', ['ended', 'active'])
            ->withCount(['positions', 'voteTokens'])
            ->orderByRaw("CASE WHEN status = 'ended' THEN 1 ELSE 2 END")
            ->orderBy('ends_at', 'desc')
            ->paginate(10);

        return view('livewire.observer.election-results-list', compact('elections'));
    }
}