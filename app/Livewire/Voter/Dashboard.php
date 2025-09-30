<?php

namespace App\Livewire\Voter;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Election\Election;
use App\Models\Candidate\Candidate;
use App\Models\Notification\Notification;
use App\Services\VoterStatsService;

class Dashboard extends Component
{
    public $search = '';
    public $filter = '';

    protected $listeners = ['candidateApproved' => 'handleApproval'];

    public function handleApproval()
    {
        // Refresh user data and redirect to candidate dashboard
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->refresh();
        return redirect()->route('candidate.dashboard');
    }

    public function render()
    {
        $dashboardData = $this->dashboardData;
        
        // Vote tokens are managed through proper accreditation process only
        
        return view('livewire.voter.dashboard');
    }
    
    // REMOVED: Unauthorized automatic token generation
    // Vote tokens are SACRED and can ONLY be generated through:
    // 1. Manual accreditation process by admin
    // 2. Voter register publication by admin
    
    #[Computed]
    public function electionStats()
    {
        return app(VoterStatsService::class)->getElectionStats(Auth::user());
    }

    #[Computed]
    public function dashboardData()
    {
        $data = app(VoterStatsService::class)->getDashboardData(Auth::user(), 4);

        // Filter elections based on search and filter
        if ($this->search || $this->filter) {
            $elections = $data['elections'];

            if ($this->search) {
                $elections = $elections->filter(function($election) {
                    return stripos($election->title, $this->search) !== false ||
                           stripos($election->description, $this->search) !== false;
                });
            }

            if ($this->filter && $this->filter !== '') {
                $elections = $elections->filter(function($election) {
                    return $election->type->value === $this->filter;
                });
            }

            $data['elections'] = $elections;
        }

        return $data;
    }

    #[Computed]
    public function accountStatus()
    {
        return Auth::user()->getAccountStatus();
    }
    
    #[Computed]
    public function recentVoteHistory()
    {
        return app(VoterStatsService::class)->getRecentVoteHistory(Auth::user(), 4);
    }

    #[Computed]
    public function kycStatus()
    {
        return Auth::user()->getKycStatus();
    }


}