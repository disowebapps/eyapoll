<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Election\Election;
use App\Models\User;
use App\Models\Voting\Vote;
use App\Models\Voting\VoteToken;
use App\Models\System\SystemMetric;
use App\Models\System\SecurityEvent;

class DashboardOptimizationService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const REALTIME_CACHE_TTL = 30; // 30 seconds for real-time data

    public function getOptimizedMetrics(string $period = '24h'): array
    {
        $cacheKey = "dashboard_optimized_metrics_{$period}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($period) {
            return [
                'overview' => $this->getOverviewMetrics($period),
                'performance' => $this->getPerformanceMetrics(),
                'trends' => $this->getTrendData($period),
                'alerts' => $this->getAlertSummary()
            ];
        });
    }

    public function getRealTimeMetrics(): array
    {
        return Cache::remember('dashboard_realtime', self::REALTIME_CACHE_TTL, function() {
            return [
                'active_sessions' => $this->getActiveSessions(),
                'current_votes' => $this->getCurrentVoteCount(),
                'system_load' => $this->getSystemLoad(),
                'security_status' => $this->getSecurityStatus()
            ];
        });
    }

    private function getOverviewMetrics(string $period): array
    {
        $timeframe = $this->getTimeframe($period);
        
        // Use single query with subqueries for better performance
        $metrics = DB::select("
            SELECT 
                (SELECT COUNT(*) FROM elections WHERE status = 'active') as active_elections,
                (SELECT COUNT(*) FROM elections WHERE status = 'scheduled') as scheduled_elections,
                (SELECT COUNT(*) FROM users WHERE email_verified_at IS NOT NULL) as verified_users,
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM vote_tokens WHERE is_used = 0 AND is_revoked = 0) as active_tokens,
                (SELECT COUNT(*) FROM votes WHERE created_at >= ?) as recent_votes
        ", [$timeframe]);

        $data = $metrics[0];
        
        return [
            'elections' => [
                'active' => $data->active_elections,
                'scheduled' => $data->scheduled_elections,
                'participation_rate' => $this->calculateParticipationRate()
            ],
            'users' => [
                'total' => $data->total_users,
                'verified' => $data->verified_users,
                'verification_rate' => $data->total_users > 0 ? round(($data->verified_users / $data->total_users) * 100, 1) : 0
            ],
            'tokens' => [
                'active' => $data->active_tokens,
                'recent_votes' => $data->recent_votes
            ]
        ];
    }

    private function getPerformanceMetrics(): array
    {
        return [
            'response_time' => $this->getAverageResponseTime(),
            'database_health' => $this->getDatabaseHealth(),
            'memory_usage' => $this->getMemoryUsage(),
            'uptime' => $this->getSystemUptime()
        ];
    }

    private function getTrendData(string $period): array
    {
        $timeframe = $this->getTimeframe($period);
        $interval = $this->getInterval($period);
        
        return [
            'user_registrations' => $this->getUserRegistrationTrend($timeframe, $interval),
            'vote_activity' => $this->getVoteActivityTrend($timeframe, $interval),
            'security_events' => $this->getSecurityEventTrend($timeframe, $interval)
        ];
    }

    private function getAlertSummary(): array
    {
        return [
            'critical' => SecurityEvent::where('severity', 'high')->whereDate('created_at', today())->count(),
            'warnings' => SecurityEvent::where('severity', 'medium')->whereDate('created_at', today())->count(),
            'info' => SecurityEvent::where('severity', 'low')->whereDate('created_at', today())->count(),
            'threat_level' => $this->calculateThreatLevel()
        ];
    }

    private function calculateParticipationRate(): float
    {
        $activeElections = Election::where('status', 'active')->get(['id']);
        if ($activeElections->isEmpty()) return 0;

        $totalRate = 0;
        foreach ($activeElections as $election) {
            $votes = Vote::where('election_id', $election->id)->count();
            $tokens = VoteToken::where('election_id', $election->id)->count();
            $totalRate += $tokens > 0 ? ($votes / $tokens) * 100 : 0;
        }

        return round($totalRate / $activeElections->count(), 1);
    }

    private function getActiveSessions(): int
    {
        return DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
            ->count();
    }

    private function getCurrentVoteCount(): int
    {
        return Vote::whereDate('created_at', today())->count();
    }

    private function getSystemLoad(): array
    {
        $load = SystemMetric::where('metric_name', 'system_load')
            ->where('recorded_at', '>=', now()->subMinutes(5))
            ->avg('value') ?? 0;

        return [
            'current' => round($load, 2),
            'status' => $load < 70 ? 'healthy' : ($load < 90 ? 'warning' : 'critical')
        ];
    }

    private function getSecurityStatus(): string
    {
        $criticalEvents = SecurityEvent::where('severity', 'high')
            ->whereDate('created_at', today())
            ->count();

        return match(true) {
            $criticalEvents >= 10 => 'critical',
            $criticalEvents >= 5 => 'high',
            $criticalEvents >= 1 => 'medium',
            default => 'low'
        };
    }

    private function getAverageResponseTime(): float
    {
        return SystemMetric::where('metric_name', 'response_time')
            ->where('recorded_at', '>=', now()->subHour())
            ->avg('value') ?? 0;
    }

    private function getDatabaseHealth(): string
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $responseTime = (microtime(true) - $start) * 1000;
            
            return $responseTime < 100 ? 'excellent' : ($responseTime < 500 ? 'good' : 'slow');
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function getMemoryUsage(): array
    {
        $usage = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        
        return [
            'current' => round($usage / 1024 / 1024, 2), // MB
            'peak' => round($peak / 1024 / 1024, 2), // MB
            'limit' => ini_get('memory_limit')
        ];
    }

    private function getSystemUptime(): float
    {
        // Calculate uptime based on system metrics
        $totalChecks = SystemMetric::where('metric_name', 'health_check')
            ->whereDate('recorded_at', today())
            ->count();
            
        $successfulChecks = SystemMetric::where('metric_name', 'health_check')
            ->where('value', 1)
            ->whereDate('recorded_at', today())
            ->count();
            
        return $totalChecks > 0 ? round(($successfulChecks / $totalChecks) * 100, 2) : 99.9;
    }

    private function getUserRegistrationTrend($since, $interval): array
    {
        return User::where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '{$interval}') as period, COUNT(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn($item) => [
                'period' => $item->period,
                'value' => $item->count
            ])
            ->toArray();
    }

    private function getVoteActivityTrend($since, $interval): array
    {
        return Vote::where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '{$interval}') as period, COUNT(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn($item) => [
                'period' => $item->period,
                'value' => $item->count
            ])
            ->toArray();
    }

    private function getSecurityEventTrend($since, $interval): array
    {
        return SecurityEvent::where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '{$interval}') as period, severity, COUNT(*) as count")
            ->groupBy('period', 'severity')
            ->orderBy('period')
            ->get()
            ->groupBy('period')
            ->map(fn($events, $period) => [
                'period' => $period,
                'low' => $events->where('severity', 'low')->sum('count'),
                'medium' => $events->where('severity', 'medium')->sum('count'),
                'high' => $events->where('severity', 'high')->sum('count')
            ])
            ->values()
            ->toArray();
    }

    private function calculateThreatLevel(): string
    {
        $criticalEvents = SecurityEvent::where('severity', 'high')
            ->whereDate('created_at', today())
            ->count();

        return match(true) {
            $criticalEvents >= 10 => 'critical',
            $criticalEvents >= 5 => 'high',
            $criticalEvents >= 1 => 'medium',
            default => 'low'
        };
    }

    private function getTimeframe(string $period): \Carbon\Carbon
    {
        return match($period) {
            '1h' => now()->subHour(),
            '24h' => now()->subDay(),
            '7d' => now()->subWeek(),
            '30d' => now()->subMonth(),
            default => now()->subDay()
        };
    }

    private function getInterval(string $period): string
    {
        return match($period) {
            '1h' => '%Y-%m-%d %H:%i',
            '24h' => '%Y-%m-%d %H:00',
            '7d' => '%Y-%m-%d',
            '30d' => '%Y-%m-%d',
            default => '%Y-%m-%d %H:00'
        };
    }

    public function clearCache(): void
    {
        $patterns = [
            'dashboard_optimized_metrics_*',
            'dashboard_realtime',
            'dashboard_overview_*',
            'election_metrics_*',
            'voter_metrics_*',
            'chart_data_*'
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}