<?php

namespace App\Services\Admin;

use App\Models\System\SystemAlert;
use App\Models\System\SecurityEvent;
use Illuminate\Support\Facades\Notification;

class AlertService
{
    public function checkThresholds()
    {
        $this->checkSecurityThresholds();
        $this->checkSystemThresholds();
        $this->checkComplianceThresholds();
    }

    private function checkSecurityThresholds()
    {
        $failedLogins = SecurityEvent::where('event_type', 'failed_login')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($failedLogins > 50) {
            $this->createAlert('security', 'critical', 'High failed login attempts detected', [
                'count' => $failedLogins,
                'threshold' => 50
            ]);
        }
    }

    private function checkSystemThresholds()
    {
        $errorRate = SecurityEvent::where('severity', 'high')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($errorRate > 10) {
            $this->createAlert('system', 'warning', 'High error rate detected', [
                'count' => $errorRate
            ]);
        }
    }

    private function checkComplianceThresholds()
    {
        // Check for overdue KYC reviews
        $overdueKyc = \App\Models\Auth\IdDocument::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        if ($overdueKyc > 0) {
            $this->createAlert('compliance', 'warning', 'Overdue KYC reviews', [
                'count' => $overdueKyc
            ]);
        }
    }

    private function createAlert(string $type, string $severity, string $message, array $metadata = [])
    {
        SystemAlert::create([
            'alert_type' => $type,
            'severity' => $severity,
            'title' => ucfirst($type) . ' Alert',
            'message' => $message,
            'metadata' => $metadata
        ]);
    }
}