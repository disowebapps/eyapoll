<?php

namespace App\Livewire\Candidate;

use Livewire\Component;
use App\Models\Candidate\Candidate;
use App\Models\Election\Election;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Dashboard extends Component
{
    use AuthorizesRequests;

    public $statistics = [];
    public $activeApplications = [];
    public $pendingApplications = [];
    public $applications = [];
    public $hasActiveApplications = false;
    public $hasPendingApplications = false;

    protected $listeners = ['refreshData' => 'loadData'];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $userId = auth()->id();

        // Load statistics
        $this->loadStatistics($userId);

        // Load applications
        $this->loadApplications($userId);

        // Categorize applications
        $this->categorizeApplications();
    }

    private function loadStatistics($userId)
    {
        $this->statistics = Cache::remember(
            "candidate_stats_{$userId}",
            300, // 5 minutes
            function () use ($userId) {
                $candidates = Candidate::where('user_id', $userId)->get();

                return [
                    'total_applications' => $candidates->count(),
                    'approved_applications' => $candidates->where('status', 'approved')->count(),
                    'pending_applications' => $candidates->where('status', 'pending')->count(),
                    'rejected_applications' => $candidates->where('status', 'rejected')->count(),
                    'won_elections' => $candidates->where('status', 'approved')->filter(function ($candidate) {
                        return $candidate->isWinner();
                    })->count(),
                    'total_votes' => $candidates->where('status', 'approved')->sum(function ($candidate) {
                        return $candidate->getVoteCount();
                    }),
                    'approval_rate' => $candidates->count() > 0 ?
                        round(($candidates->where('status', 'approved')->count() / $candidates->count()) * 100, 1) : 0,
                    'win_rate' => $candidates->where('status', 'approved')->count() > 0 ?
                        round(($candidates->where('status', 'approved')->filter(function ($candidate) {
                            return $candidate->isWinner();
                        })->count() / $candidates->where('status', 'approved')->count()) * 100, 1) : 0,
                ];
            }
        );
    }

    private function loadApplications($userId)
    {
        $this->applications = Candidate::with(['election', 'position'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'election_title' => $application->election->title,
                    'election_type' => $application->election->type->label(),
                    'position_title' => $application->position->title,
                    'status' => $application->status->value,
                    'status_label' => $application->status->label(),
                    'status_color' => match ($application->status->value) {
                        'pending' => 'yellow',
                        'approved' => 'green',
                        'rejected' => 'red',
                        'suspended' => 'red',
                        'withdrawn' => 'gray',
                        default => 'gray',
                    },
                    'created_at' => $application->created_at,
                    'can_withdraw' => $application->canWithdraw(),
                    'has_vote_results' => $application->election->hasResults(),
                    'vote_count' => $application->getVoteCount(),
                    'vote_percentage' => $application->getVotePercentage(),
                    'ranking' => $application->getRanking(),
                    'is_winner' => $application->isWinner(),
                    'progress' => $application->getApplicationProgress(),
                ];
            })
            ->toArray();
    }

    private function categorizeApplications()
    {
        $this->activeApplications = array_filter($this->applications, function ($app) {
            return in_array($app['status'], ['approved']);
        });

        $this->pendingApplications = array_filter($this->applications, function ($app) {
            return in_array($app['status'], ['pending']);
        });

        $this->hasActiveApplications = !empty($this->activeApplications);
        $this->hasPendingApplications = !empty($this->pendingApplications);
    }

    public function viewApplication($applicationId)
    {
        return redirect()->route('candidate.application', $applicationId);
    }

    public function withdrawApplication($applicationId)
    {
        $application = Candidate::findOrFail($applicationId);

        // Ensure the application belongs to the current user
        if ($application->user_id !== auth()->id()) {
            session()->flash('error', 'You do not have permission to withdraw this application.');
            return;
        }

        if (!$application->canWithdraw()) {
            session()->flash('error', 'This application cannot be withdrawn at this time.');
            return;
        }

        $application->update(['status' => 'withdrawn']);

        session()->flash('success', 'Application withdrawn successfully.');
        $this->loadData();
    }

    public function viewElectionResults($applicationId)
    {
        $application = Candidate::findOrFail($applicationId);

        // Ensure the application belongs to the current user
        if ($application->user_id !== auth()->id()) {
            session()->flash('error', 'You do not have permission to view these results.');
            return;
        }

        return redirect()->route('admin.elections.show', $application->election_id);
    }

    public function refreshData()
    {
        Cache::forget("candidate_stats_" . auth()->id());
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.candidate.dashboard');
    }
}