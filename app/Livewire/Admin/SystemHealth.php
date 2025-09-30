<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Audit\AuditLog;
use App\Models\Election\Election;
use App\Models\User;

class SystemHealth extends Component
{
    public $healthChecks = [];
    public $systemMetrics = [];
    public $securityAlerts = [];

    public function mount()
    {
        $this->runHealthChecks();
        $this->loadSystemMetrics();
        $this->loadSecurityAlerts();
    }

    public function refreshHealth()
    {
        $this->runHealthChecks();
        $this->loadSystemMetrics();
        session()->flash('success', 'System health refreshed.');
    }

    private function runHealthChecks()
    {
        $this->healthChecks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'memory' => $this->checkMemory(),
            'disk_space' => $this->checkDiskSpace(),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $responseTime = $this->measureDatabaseResponseTime();
            
            return [
                'status' => 'healthy',
                'message' => 'Database connection active',
                'response_time' => $responseTime . 'ms',
                'color' => 'green'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
                'color' => 'red'
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 10);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);
            
            if ($retrieved === 'test') {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache system operational',
                    'color' => 'green'
                ];
            }
            
            throw new \Exception('Cache test failed');
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache system error',
                'error' => $e->getMessage(),
                'color' => 'red'
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            Storage::put($testFile, 'test');
            $content = Storage::get($testFile);
            Storage::delete($testFile);
            
            if ($content === 'test') {
                return [
                    'status' => 'healthy',
                    'message' => 'Storage system operational',
                    'color' => 'green'
                ];
            }
            
            throw new \Exception('Storage test failed');
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage system error',
                'error' => $e->getMessage(),
                'color' => 'red'
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            
            $status = 'healthy';
            $color = 'green';
            $message = 'Queue system operational';
            
            if ($pendingJobs > 1000) {
                $status = 'warning';
                $color = 'yellow';
                $message = 'High queue backlog detected';
            }
            
            if ($failedJobs > 50) {
                $status = 'error';
                $color = 'red';
                $message = 'High failed job count';
            }
            
            return [
                'status' => $status,
                'message' => $message,
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
                'color' => $color
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue system error',
                'error' => $e->getMessage(),
                'color' => 'red'
            ];
        }
    }

    private function checkMemory(): array
    {
        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // MB
        $memoryLimit = ini_get('memory_limit');
        
        $status = 'healthy';
        $color = 'green';
        
        if ($memoryUsage > 128) {
            $status = 'warning';
            $color = 'yellow';
        }
        
        if ($memoryUsage > 256) {
            $status = 'critical';
            $color = 'red';
        }
        
        return [
            'status' => $status,
            'message' => round($memoryUsage, 2) . ' MB used',
            'usage' => $memoryUsage,
            'limit' => $memoryLimit,
            'color' => $color
        ];
    }

    private function checkDiskSpace(): array
    {
        $freeBytes = disk_free_space('/');
        $totalBytes = disk_total_space('/');
        $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;
        
        $status = 'healthy';
        $color = 'green';
        
        if ($usedPercent > 80) {
            $status = 'warning';
            $color = 'yellow';
        }
        
        if ($usedPercent > 90) {
            $status = 'critical';
            $color = 'red';
        }
        
        return [
            'status' => $status,
            'message' => round($usedPercent, 1) . '% used',
            'used_percent' => $usedPercent,
            'free_gb' => round($freeBytes / 1024 / 1024 / 1024, 2),
            'color' => $color
        ];
    }

    private function measureDatabaseResponseTime(): int
    {
        $start = microtime(true);
        DB::select('SELECT 1');
        $end = microtime(true);
        
        return round(($end - $start) * 1000);
    }

    private function loadSystemMetrics()
    {
        $this->systemMetrics = [
            'active_elections' => Election::active()->count(),
            'total_users' => User::count(),
            'pending_approvals' => User::pending()->count() + 
                                  \App\Models\Candidate\Candidate::where('status', 'pending')->count(),
            'recent_logins' => AuditLog::where('action', 'login')
                                     ->where('created_at', '>=', now()->subHours(24))
                                     ->count(),
            'system_uptime' => $this->getSystemUptime(),
        ];
    }

    private function loadSecurityAlerts()
    {
        $this->securityAlerts = [];
        
        // Check for failed login attempts
        $failedLogins = AuditLog::where('action', 'failed_login')
                               ->where('created_at', '>=', now()->subHour())
                               ->count();
        
        if ($failedLogins > 10) {
            $this->securityAlerts[] = [
                'type' => 'warning',
                'title' => 'High Failed Login Attempts',
                'message' => "{$failedLogins} failed login attempts in the last hour",
                'action' => 'Review security logs'
            ];
        }
        
        // Check for pending approvals
        $pendingCount = User::pending()->count();
        if ($pendingCount > 20) {
            $this->securityAlerts[] = [
                'type' => 'info',
                'title' => 'High Pending Approvals',
                'message' => "{$pendingCount} users awaiting approval",
                'action' => 'Review pending users'
            ];
        }
    }

    private function getSystemUptime(): string
    {
        // Mock implementation - would need actual system monitoring
        return '99.9% (30 days)';
    }

    public function render()
    {
        $overallHealth = $this->calculateOverallHealth();
        
        return view('livewire.admin.system-health', [
            'overallHealth' => $overallHealth
        ]);
    }

    private function calculateOverallHealth(): array
    {
        $healthyCount = 0;
        $totalChecks = count($this->healthChecks);
        
        foreach ($this->healthChecks as $check) {
            if ($check['status'] === 'healthy') {
                $healthyCount++;
            }
        }
        
        $percentage = $totalChecks > 0 ? ($healthyCount / $totalChecks) * 100 : 0;
        
        $status = 'healthy';
        $color = 'green';
        
        if ($percentage < 100) {
            $status = 'warning';
            $color = 'yellow';
        }
        
        if ($percentage < 70) {
            $status = 'critical';
            $color = 'red';
        }
        
        return [
            'status' => $status,
            'percentage' => round($percentage, 1),
            'color' => $color
        ];
    }
}