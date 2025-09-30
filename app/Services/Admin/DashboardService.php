<?php

namespace App\Services\Admin;

use App\Models\System\SystemMetric;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getMetrics(): array
    {
        return Cache::remember('dashboard_metrics', 300, function() {
            return [
                'users' => $this->getUserMetrics(),
                'security' => $this->getSecurityMetrics(),
                'elections' => $this->getElectionMetrics(),
                'system' => $this->getSystemHealth()
            ];
        });
    }

    private function getUserMetrics(): array
    {
        return [
            'active' => SystemMetric::latest('active_users'),
            'pending_kyc' => SystemMetric::latest('pending_kyc')
        ];
    }

    private function getSecurityMetrics(): array
    {
        return [
            'failed_logins' => SystemMetric::latest('failed_logins_hourly'),
            'blocked_ips' => SystemMetric::latest('blocked_ips')
        ];
    }

    private function getElectionMetrics(): array
    {
        $activeElection = \App\Models\Election\Election::where('status', 'ongoing')->first();
        
        if (!$activeElection) {
            return [
                'active_elections' => SystemMetric::latest('active_elections_count') ?? 0,
                'total_votes' => SystemMetric::latest('total_votes_cast') ?? 0,
                'turnout_rates' => [],
                'geographic_distribution' => [],
                'demographic_analysis' => [],
                'participation_trends' => [],
                'role_based_metrics' => []
            ];
        }
        
        return [
            'active_elections' => SystemMetric::latest('active_elections_count') ?? 0,
            'total_votes' => SystemMetric::latest('total_votes_cast') ?? 0,
            'turnout_rates' => $this->getTurnoutRates($activeElection),
            'geographic_distribution' => $this->getGeographicDistribution($activeElection),
            'demographic_analysis' => $this->getDemographicAnalysis($activeElection),
            'participation_trends' => $this->getParticipationTrends($activeElection),
            'role_based_metrics' => $this->getRoleBasedMetrics($activeElection)
        ];
    }
    
    private function getRoleBasedMetrics($election): array
    {
        $prefix = "election_{$election->id}";
        return [
            'admin_activities' => SystemMetric::latest("{$prefix}_admin_activities") ?? 0,
            'observer_reports' => SystemMetric::latest("{$prefix}_observer_reports") ?? 0,
            'active_candidates' => SystemMetric::latest("{$prefix}_active_candidates") ?? 0,
            'voter_engagement' => SystemMetric::latest("{$prefix}_voter_engagement") ?? 0
        ];
    }
    
    private function getTurnoutRates($election): array
    {
        $prefix = "election_{$election->id}";
        return [
            'current_rate' => SystemMetric::latest("{$prefix}_turnout_rate") ?? 0,
            'total_votes' => SystemMetric::latest("{$prefix}_total_votes") ?? 0,
            'eligible_voters' => SystemMetric::latest("{$prefix}_eligible_voters") ?? 0,
            'votes_last_hour' => SystemMetric::latest("{$prefix}_votes_last_hour") ?? 0
        ];
    }
    
    private function getGeographicDistribution($election): array
    {
        $prefix = "election_{$election->id}";
        $regions = ['north', 'south', 'east', 'west', 'central'];
        $distribution = [];
        
        foreach ($regions as $region) {
            $distribution[$region] = SystemMetric::latest("{$prefix}_votes_{$region}") ?? 0;
        }
        
        return $distribution;
    }
    
    private function getDemographicAnalysis($election): array
    {
        $prefix = "election_{$election->id}";
        $ageGroups = ['18-25', '26-35', '36-50', '51-65', '65+'];
        $demographics = [];
        
        foreach ($ageGroups as $group) {
            $demographics[$group] = SystemMetric::latest("{$prefix}_votes_age_{$group}") ?? 0;
        }
        
        return $demographics;
    }
    
    private function getParticipationTrends($election): array
    {
        $prefix = "election_{$election->id}";
        $trends = [];
        
        // Get last 24 hours of data
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i)->format('H:00');
            $trends[] = [
                'time' => $hour,
                'votes' => SystemMetric::where('metric_name', "{$prefix}_votes_last_hour")
                    ->where('recorded_at', '>=', now()->subHours($i+1))
                    ->where('recorded_at', '<', now()->subHours($i))
                    ->sum('value') ?? 0
            ];
        }
        
        return $trends;
    }

    private function getSystemHealth(): array
    {
        return [
            'status' => 'healthy',
            'uptime' => 99.9,
            'response_time' => SystemMetric::latest('api_response_time') ?? 150
        ];
    }
}