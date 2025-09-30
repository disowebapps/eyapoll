<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\System\SystemMetric;
use Illuminate\Support\Facades\DB;

class CollectSystemMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        $this->collectCoreMetrics();
        $this->collectSecurityMetrics();
        $this->collectElectionMetrics();
    }

    private function collectCoreMetrics()
    {
        SystemMetric::record('active_users', \App\Models\User::where('status', 'approved')->count());
        SystemMetric::record('pending_kyc', \App\Models\Auth\IdDocument::where('status', 'pending')->count());
        SystemMetric::record('active_elections', \App\Models\Election\Election::where('status', 'active')->count());
    }

    private function collectSecurityMetrics()
    {
        $failedLogins = \App\Models\Security\LoginAttempt::where('successful', false)
            ->where('attempted_at', '>=', now()->subHour())->count();
        SystemMetric::record('failed_logins_hourly', $failedLogins);
    }

    private function collectElectionMetrics()
    {
        foreach (\App\Models\Election\Election::whereIn('status', ['ongoing', 'completed'])->get() as $election) {
            $this->collectElectionSpecificMetrics($election);
        }
        
        // Global metrics
        SystemMetric::record('total_votes_cast', \App\Models\Voting\VoteRecord::count());
        SystemMetric::record('active_elections_count', \App\Models\Election\Election::where('status', 'ongoing')->count());
    }
    
    private function collectElectionSpecificMetrics($election)
    {
        $prefix = "election_{$election->id}";
        
        // Turnout metrics
        $turnout = $election->getVoterTurnout();
        SystemMetric::record("{$prefix}_turnout_rate", $turnout['percentage'], '%');
        SystemMetric::record("{$prefix}_total_votes", $turnout['total_voted']);
        SystemMetric::record("{$prefix}_eligible_voters", $turnout['total_eligible']);
        
        // Participation trends (hourly)
        $hourlyVotes = \App\Models\Voting\VoteRecord::where('election_id', $election->id)
            ->where('cast_at', '>=', now()->subHour())
            ->count();
        SystemMetric::record("{$prefix}_votes_last_hour", $hourlyVotes);
        
        // Geographic distribution (if location data exists)
        $this->collectGeographicMetrics($election, $prefix);
        
        // Demographic analysis (if demographic data exists)
        $this->collectDemographicMetrics($election, $prefix);
        
        // Role-based metrics
        $this->collectRoleBasedMetrics($election, $prefix);
    }
    
    private function collectRoleBasedMetrics($election, $prefix)
    {
        // Admin activities
        $adminActions = \App\Models\Audit\AuditLog::where('created_at', '>=', now()->subHour())
            ->whereIn('action', ['user_approved', 'user_rejected', 'election_started', 'election_ended'])
            ->count();
        SystemMetric::record("{$prefix}_admin_activities", $adminActions);
        
        // Observer reports
        $observerReports = \App\Models\Observer\ObserverAlert::where('created_at', '>=', now()->subHour())
            ->count();
        SystemMetric::record("{$prefix}_observer_reports", $observerReports);
        
        // Candidate statuses
        $activeCandidates = \App\Models\Candidate\Candidate::where('election_id', $election->id)
            ->where('status', 'approved')
            ->count();
        SystemMetric::record("{$prefix}_active_candidates", $activeCandidates);
        
        // Voter engagement (tokens used vs issued)
        $engagementRate = $turnout['total_eligible'] > 0 
            ? round(($turnout['total_voted'] / $turnout['total_eligible']) * 100, 1)
            : 0;
        SystemMetric::record("{$prefix}_voter_engagement", $engagementRate, '%');
    }
    
    private function collectGeographicMetrics($election, $prefix)
    {
        // Mock implementation - replace with actual geographic data
        $regions = ['north', 'south', 'east', 'west', 'central'];
        foreach ($regions as $region) {
            $count = rand(10, 100); // Replace with actual query
            SystemMetric::record("{$prefix}_votes_{$region}", $count);
        }
    }
    
    private function collectDemographicMetrics($election, $prefix)
    {
        // Age groups
        $ageGroups = ['18-25', '26-35', '36-50', '51-65', '65+'];
        foreach ($ageGroups as $group) {
            $count = rand(5, 50); // Replace with actual demographic query
            SystemMetric::record("{$prefix}_votes_age_{$group}", $count);
        }
    }
}