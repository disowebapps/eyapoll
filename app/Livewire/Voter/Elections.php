<?php

namespace App\Livewire\Voter;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Services\VoterStatsService;
use Illuminate\Support\Facades\Auth;

class Elections extends Component
{
    use WithPagination;

    public $perPage = 6;
    public $statusFilter = 'all'; // 'all', 'upcoming', 'ongoing', 'completed', 'cancelled', 'archived'

    #[Computed]
    public function votingData()
    {
        return app(VoterStatsService::class)->getElectionsData(Auth::user(), $this->perPage, $this->statusFilter);
    }

    public function loadMore()
    {
        $this->perPage += 6;
    }

    public function render()
    {
        return view('livewire.voter.elections');
    }
}