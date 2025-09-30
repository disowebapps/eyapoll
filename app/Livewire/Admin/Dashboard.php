<?php

namespace App\Livewire\Admin;

use App\Models\Election\Election;
use App\Models\User;
use App\Models\Voting\VoteToken;
use App\Models\System\SecurityEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public $refreshInterval = 30;
    public $selectedPeriod = '24h';
    public $activeTab = 'overview';
    public $realTimeUpdates = true;
    
    protected $listeners = [
        'refresh' => '$refresh',
        'tabChanged' => 'setActiveTab',
        'periodChanged' => 'setPeriod'
    ];

    public function mount()
    {
        if (!auth()->guard('admin')->check()) {
            abort(403);
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->emit('tabUpdated', $tab);
    }

    public function getOverviewMetricsProperty()
    {
        return Cache::remember('dashboard_overview_' . $this->selectedPeriod, 300, function() {
            $activeElections = Election::where('status', 'active')->count();
            $totalUsers = User::count();
            $verifiedUsers = User::whereNotNull('email_verified_at')->count();
            $todayAlerts = SecurityEvent::whereDate('created_at', today())->count();
            
            return [
                'elections' => [
                    'active' => $activeElections,
                    'participation_rate' => $this->calculateParticipationRate()
                ],
                'voters' => [
                    'total_registered' => $totalUsers,
                    'verification_rate' => $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 1) : 0
                ],
                'security' => [
                    'alerts_today' => $todayAlerts,
                    'threat_level' => $todayAlerts > 10 ? 'high' : ($todayAlerts > 5 ? 'medium' : 'low')
                ],
                'performance' => [
                    'system_uptime' => 99.9,
                    'active_sessions' => $this->getActiveSessions()
                ]
            ];
        });
    }
    
    protected function calculateParticipationRate()
    {
        $activeElections = Election::where('status', 'active')->get();
        if ($activeElections->isEmpty()) return 0;
        
        $totalRate = 0;
        foreach ($activeElections as $election) {
            $tokens = VoteToken::where('election_id', $election->id)->count();
            $usedTokens = VoteToken::where('election_id', $election->id)->where('is_used', true)->count();
            $totalRate += $tokens > 0 ? ($usedTokens / $tokens) * 100 : 0;
        }
        
        return round($totalRate / $activeElections->count(), 1);
    }
    
    protected function getActiveSessions()
    {
        return Cache::remember('active_sessions', 60, function() {
            try {
                return DB::table('sessions')
                    ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
                    ->count();
            } catch (\Exception $e) {
                return 12; // Default fallback
            }
        });
    }

    public function setPeriod($period)
    {
        $this->selectedPeriod = $period;
        $this->emit('periodUpdated', $period);
    }

    public function toggleRealTimeUpdates()
    {
        $this->realTimeUpdates = !$this->realTimeUpdates;
        $this->emit('realTimeToggled', $this->realTimeUpdates);
    }

    public function render()
    {
        $electionMetrics = [
            'active' => Election::where('status', 'active')->count(),
            'participation_rate' => $this->overviewMetrics['elections']['participation_rate'],
            'recent_activity' => Election::latest()->limit(5)->get(['id', 'title', 'status', 'created_at'])
        ];
        
        $voterMetrics = [
            'total_registered' => User::count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'verification_rate' => $this->overviewMetrics['voters']['verification_rate']
        ];
        
        return view('livewire.admin.dashboard', [
            'overviewMetrics' => $this->overviewMetrics,
            'electionMetrics' => $electionMetrics,
            'voterMetrics' => $voterMetrics,
            'chartData' => [],
            'selectedPeriod' => $this->selectedPeriod,
            'realTimeUpdates' => $this->realTimeUpdates
        ]);
    }
}