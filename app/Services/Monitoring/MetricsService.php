<?php

namespace App\Services;

use App\Models\SystemMetric;
use App\Models\SecurityEvent;
use App\Models\SystemAlert;
use Carbon\Carbon;

class MetricsService
{
    public function recordMetric(string $name, $value, ?string $unit = null, ?array $metadata = null)
    {
        return SystemMetric::create([
            'metric_name' => $name,
            'value' => $value,
            'unit' => $unit,
            'metadata' => $metadata,
            'recorded_at' => now()
        ]);
    }

    public function logSecurityEvent(string $type, string $severity, string $description, ?array $metadata = null)
    {
        return SecurityEvent::create([
            'event_type' => $type,
            'severity' => $severity,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata
        ]);
    }

    public function createAlert(string $type, string $severity, string $title, string $message, ?array $metadata = null)
    {
        return SystemAlert::create([
            'alert_type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'metadata' => $metadata
        ]);
    }

    public function getMetricHistory(string $name, Carbon $from, Carbon $to)
    {
        return SystemMetric::where('metric_name', $name)
            ->whereBetween('recorded_at', [$from, $to])
            ->orderBy('recorded_at')
            ->get();
    }

    public function getDailyAverage(string $name, Carbon $date)
    {
        return SystemMetric::where('metric_name', $name)
            ->whereDate('recorded_at', $date)
            ->avg('value');
    }
}