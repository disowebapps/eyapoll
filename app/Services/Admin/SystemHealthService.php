<?php

namespace App\Services\Admin;

use App\Models\System\SystemMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SystemHealthService
{
    public function recordMetrics()
    {
        $this->recordDatabaseMetrics();
        $this->recordPerformanceMetrics();
        $this->recordSecurityMetrics();
    }

    private function recordDatabaseMetrics()
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $responseTime = (microtime(true) - $start) * 1000;
            
            SystemMetric::record('database_response_time', $responseTime, 'ms');
            
            $connections = DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 0;
            SystemMetric::record('database_connections', $connections);
            
        } catch (\Exception $e) {
            SystemMetric::record('database_errors', 1);
        }
    }

    private function recordPerformanceMetrics()
    {
        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // MB
        SystemMetric::record('memory_usage', $memoryUsage, 'MB');
        
        $loadAverage = sys_getloadavg()[0] ?? 0;
        SystemMetric::record('cpu_load', $loadAverage);
    }

    private function recordSecurityMetrics()
    {
        $failedLogins = \App\Models\Security\LoginAttempt::where('successful', false)
            ->where('attempted_at', '>=', now()->subHour())
            ->count();
            
        SystemMetric::record('failed_logins_hourly', $failedLogins);
        
        $blockedIps = \App\Models\Security\IpBlock::where('blocked_until', '>', now())
            ->count();
            
        SystemMetric::record('blocked_ips', $blockedIps);
    }

    public function getHealthScore()
    {
        $metrics = [
            'database' => $this->getDatabaseHealth(),
            'performance' => $this->getPerformanceHealth(),
            'security' => $this->getSecurityHealth()
        ];

        $totalScore = array_sum($metrics);
        return round($totalScore / count($metrics), 1);
    }

    private function getDatabaseHealth()
    {
        $responseTime = SystemMetric::byName('database_response_time')
            ->recent(1)
            ->avg('value') ?? 0;
            
        return $responseTime < 100 ? 100 : max(0, 100 - ($responseTime - 100));
    }

    private function getPerformanceHealth()
    {
        $memoryUsage = SystemMetric::byName('memory_usage')
            ->recent(1)
            ->avg('value') ?? 0;
            
        return $memoryUsage < 512 ? 100 : max(0, 100 - (($memoryUsage - 512) / 10));
    }

    private function getSecurityHealth()
    {
        $failedLogins = SystemMetric::byName('failed_logins_hourly')
            ->recent(1)
            ->sum('value') ?? 0;
            
        return $failedLogins < 10 ? 100 : max(0, 100 - ($failedLogins - 10) * 2);
    }
}