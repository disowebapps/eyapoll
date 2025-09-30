<?php

namespace App\Livewire\Observer;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Election\Election;
use App\Models\Audit\AuditLog;
use App\Models\Voting\VoteRecord;
use App\Enums\Audit\AuditEventType;
use App\Services\Audit\AuditDisplayService;

class Dashboard extends Component
{
    public $stats = [];
    public $recentElections = [];
    public $recentActivity = [];
    public $systemHealth = [];
    public $autoRefresh = true;
    public $selectedSeverity = 'all';
    public $expandedLogs = [];
    public $electionStatusFilter = 'all';

    public function mount()
    {
        $this->loadStats();
        $this->loadRecentElections();
        $this->loadRecentActivity();
        $this->loadSystemHealth();
    }

    public function loadStats()
    {
        $this->stats = Cache::remember('observer_dashboard_stats', 300, function () {
            return [
                'total_elections' => Election::count(),
                'active_elections' => Election::where('status', 'active')->count(),
                'total_votes' => VoteRecord::count(),
                'recent_logs' => AuditLog::where('created_at', '>=', now()->subDay())->count(),
            ];
        });
    }

    public function loadRecentElections()
    {
        $cacheKey = 'observer_recent_elections_' . $this->electionStatusFilter;
        $this->recentElections = Cache::remember($cacheKey, 300, function () {
            return Election::with(['positions'])
                ->when($this->electionStatusFilter !== 'all', fn($q) => $q->where('status', $this->electionStatusFilter))
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($election) {
                    return [
                        'id' => $election->id,
                        'title' => $election->title,
                        'status' => $election->status->label(),
                        'status_color' => $election->status->color(),
                        'positions_count' => $election->positions->count(),
                        'votes_count' => VoteRecord::where('election_id', $election->id)->count(),
                        'created_at' => $election->created_at,
                    ];
                });
        });
    }

    public function loadRecentActivity()
    {
        $auditService = app(AuditDisplayService::class);
        $this->recentActivity = $auditService->getRecentActivity(10, $this->selectedSeverity);
    }



    private function formatEntityType($entityType)
    {
        if (!$entityType) return 'System';
        
        $parts = explode('\\', $entityType);
        $className = end($parts);
        
        return match($className) {
            'Candidate' => 'Candidate',
            'User' => 'User',
            'Election' => 'Election',
            'Vote' => 'Vote',
            'Admin' => 'Admin',
            'Observer' => 'Observer',
            default => $className
        };
    }

    public function viewElectionResults($electionId)
    {
        return redirect()->route('observer.election-results', $electionId);
    }

    public function viewAllElections()
    {
        return redirect()->route('observer.elections');
    }

    public function viewAuditLogs()
    {
        return redirect()->route('observer.audit-logs');
    }

    public function viewVoteStatistics()
    {
        // Get the most recent election with votes to show results
        $election = Election::whereExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('vote_records')
                      ->whereColumn('vote_records.election_id', 'elections.id');
            })
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($election) {
            return redirect()->route('observer.election-results', $election->id);
        }
        
        // Fallback to elections page if no election with votes found
        return redirect()->route('observer.elections');
    }

    public function viewActiveElections()
    {
        return redirect()->route('observer.elections', ['status' => 'active']);
    }

    public function loadSystemHealth()
    {
        $this->systemHealth = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'overall' => true,
        ];
        $this->systemHealth['overall'] = $this->systemHealth['database'] && $this->systemHealth['cache'] && $this->systemHealth['storage'];
    }

    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkCache()
    {
        try {
            Cache::put('health_check', 'ok', 1);
            return Cache::get('health_check') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkStorage()
    {
        try {
            return is_writable(storage_path());
        } catch (\Exception $e) {
            return false;
        }
    }

    public function refreshData()
    {
        Cache::forget('observer_dashboard_stats');
        Cache::forget('observer_recent_elections');
        Cache::forget('observer_recent_activity');

        $this->loadStats();
        $this->loadRecentElections();
        $this->loadRecentActivity();
        $this->loadSystemHealth();

        $this->dispatch('stats-refreshed');
        $this->dispatch('notify', ['message' => 'Dashboard refreshed', 'type' => 'success']);
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function filterBySeverity($severity)
    {
        $this->selectedSeverity = $severity;
        $this->loadRecentActivity();
    }

    public function filterElectionsByStatus($status)
    {
        $this->electionStatusFilter = $status;
        $this->loadRecentElections();
    }

    public function toggleLogExpansion($logId)
    {
        if (in_array($logId, $this->expandedLogs)) {
            $this->expandedLogs = array_diff($this->expandedLogs, [$logId]);
        } else {
            $this->expandedLogs[] = $logId;
        }
    }

    public function render()
    {
        return view('livewire.observer.dashboard');
    }

    public function getListeners()
    {
        return [
            'echo:audit-logs,AuditLogCreated' => 'handleNewAuditLog',
            'echo:elections,ElectionStatusChanged' => 'handleElectionUpdate',
        ];
    }

    public function handleNewAuditLog($event)
    {
        $this->loadRecentActivity();
        $this->dispatch('new-audit-log', ['log' => $event]);
    }

    public function handleElectionUpdate($event)
    {
        $this->loadRecentElections();
        $this->loadStats();
    }
}
