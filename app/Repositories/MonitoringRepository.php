<?php

namespace App\Repositories;

use App\Domains\Monitoring\Repository\MonitoringRepositoryInterface;
use App\Domains\Monitoring\Entities\SystemMetric;
use App\Domains\Monitoring\Entities\SystemAlert;
use App\Domains\Monitoring\ValueObjects\MetricType;
use App\Models\System\SystemMetric as SystemMetricModel;
use App\Models\System\SystemAlert as SystemAlertModel;
use Illuminate\Support\Collection;

class MonitoringRepository implements MonitoringRepositoryInterface
{
    public function saveMetric(SystemMetric $metric): void
    {
        $model = SystemMetricModel::updateOrCreate(
            ['id' => $metric->getId()],
            [
                'metric_type' => $metric->getType()->getType(),
                'name' => $metric->getName()->getName(),
                'value' => $metric->getValue()->getValue(),
                'metadata' => $metric->getMetadata(),
                'recorded_at' => $metric->getRecordedAt(),
            ]
        );

        // Update the domain entity with the actual ID
        $reflection = new \ReflectionClass($metric);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($metric, $model->id);
    }

    public function findMetricById(int $id): ?SystemMetric
    {
        $model = SystemMetricModel::find($id);
        return $model ? $this->modelToDomainMetric($model) : null;
    }

    public function getMetricsByType(MetricType $type): Collection
    {
        return SystemMetricModel::where('metric_type', $type->getType())
            ->get()
            ->map(fn($model) => $this->modelToDomainMetric($model));
    }

    public function getRecentMetrics(int $limit = 100): Collection
    {
        return SystemMetricModel::orderBy('recorded_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($model) => $this->modelToDomainMetric($model));
    }

    public function saveAlert(SystemAlert $alert): void
    {
        $model = SystemAlertModel::updateOrCreate(
            ['id' => $alert->getId()],
            [
                'alert_type' => $alert->getType()->getType(),
                'severity' => $alert->getSeverity()->getSeverity(),
                'status' => $alert->getStatus()->getStatus(),
                'message' => $alert->getMessage(),
                'context' => $alert->getContext(),
            ]
        );

        // Update the domain entity with the actual ID
        $reflection = new \ReflectionClass($alert);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($alert, $model->id);
    }

    public function findAlertById(int $id): ?SystemAlert
    {
        $model = SystemAlertModel::find($id);
        return $model ? $this->modelToDomainAlert($model) : null;
    }

    public function getActiveAlerts(): Collection
    {
        return SystemAlertModel::where('status', 'active')
            ->get()
            ->map(fn($model) => $this->modelToDomainAlert($model));
    }

    public function getAlertsBySeverity(string $severity): Collection
    {
        return SystemAlertModel::where('severity', $severity)
            ->get()
            ->map(fn($model) => $this->modelToDomainAlert($model));
    }

    public function updateAlertStatus(SystemAlert $alert): void
    {
        SystemAlertModel::where('id', $alert->getId())->update([
            'status' => $alert->getStatus()->getStatus(),
            'resolved_at' => $alert->getResolvedAt(),
            'resolved_by' => $alert->getResolvedBy(),
        ]);
    }

    public function getSystemHealthMetrics(): array
    {
        // Implementation for system health metrics
        return [
            'cpu_usage' => SystemMetricModel::where('metric_type', 'cpu')->avg('value') ?? 0,
            'memory_usage' => SystemMetricModel::where('metric_type', 'memory')->avg('value') ?? 0,
            'disk_usage' => SystemMetricModel::where('metric_type', 'disk')->avg('value') ?? 0,
        ];
    }

    public function getAlertStatistics(): array
    {
        return [
            'total_alerts' => SystemAlertModel::count(),
            'active_alerts' => SystemAlertModel::where('status', 'active')->count(),
            'resolved_alerts' => SystemAlertModel::where('status', 'resolved')->count(),
            'critical_alerts' => SystemAlertModel::where('severity', 'critical')->count(),
        ];
    }

    private function modelToDomainMetric(SystemMetricModel $model): SystemMetric
    {
        $metric = new SystemMetric(
            new MetricType($model->metric_type),
            new \App\Domains\Monitoring\ValueObjects\MetricName($model->name),
            new \App\Domains\Monitoring\ValueObjects\MetricValue($model->value),
            $model->metadata ?? []
        );

        // Set the ID and recorded_at
        $reflection = new \ReflectionClass($metric);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($metric, $model->id);

        $recordedAtProperty = $reflection->getProperty('recordedAt');
        $recordedAtProperty->setAccessible(true);
        $recordedAtProperty->setValue($metric, $model->recorded_at);

        return $metric;
    }

    private function modelToDomainAlert(SystemAlertModel $model): SystemAlert
    {
        $alert = new SystemAlert(
            new \App\Domains\Monitoring\ValueObjects\AlertType($model->alert_type),
            new \App\Domains\Monitoring\ValueObjects\AlertSeverity($model->severity),
            $model->message,
            $model->context ?? []
        );

        // Set the ID and status
        $reflection = new \ReflectionClass($alert);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($alert, $model->id);

        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($alert, new \App\Domains\Monitoring\ValueObjects\AlertStatus($model->status));

        if ($model->resolved_at) {
            $resolvedAtProperty = $reflection->getProperty('resolvedAt');
            $resolvedAtProperty->setAccessible(true);
            $resolvedAtProperty->setValue($alert, $model->resolved_at);
        }

        if ($model->resolved_by) {
            $resolvedByProperty = $reflection->getProperty('resolvedBy');
            $resolvedByProperty->setAccessible(true);
            $resolvedByProperty->setValue($alert, $model->resolved_by);
        }

        return $alert;
    }
}