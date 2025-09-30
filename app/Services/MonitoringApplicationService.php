<?php

namespace App\Services;

use App\Domains\Monitoring\Aggregates\MonitoringAggregate;
use App\Domains\Monitoring\Repository\MonitoringRepositoryInterface;
use App\Domains\Monitoring\Entities\SystemMetric;
use App\Domains\Monitoring\Entities\SystemAlert;
use App\Domains\Monitoring\ValueObjects\MetricType;
use App\Domains\Monitoring\ValueObjects\MetricName;
use App\Domains\Monitoring\ValueObjects\MetricValue;
use App\Domains\Monitoring\ValueObjects\AlertType;
use App\Domains\Monitoring\ValueObjects\AlertSeverity;
use Illuminate\Support\Collection;

class MonitoringApplicationService
{
    private MonitoringAggregate $monitoringAggregate;
    private MonitoringRepositoryInterface $monitoringRepository;

    public function __construct(MonitoringRepositoryInterface $monitoringRepository)
    {
        $this->monitoringRepository = $monitoringRepository;
        $this->monitoringAggregate = new MonitoringAggregate();
    }

    public function recordMetric(string $type, string $name, float $value, array $metadata = []): SystemMetric
    {
        $metric = $this->monitoringAggregate->recordMetric(
            new MetricType($type),
            new MetricName($name),
            new MetricValue($value),
            $metadata
        );

        $this->monitoringRepository->saveMetric($metric);

        return $metric;
    }

    public function createAlert(string $type, string $severity, string $message, array $context = []): SystemAlert
    {
        $alert = $this->monitoringAggregate->createAlert(
            new AlertType($type),
            new AlertSeverity($severity),
            $message,
            $context
        );

        $this->monitoringRepository->saveAlert($alert);

        return $alert;
    }

    public function resolveAlert(int $alertId, int $resolverId): void
    {
        $alert = $this->monitoringRepository->findAlertById($alertId);

        if (!$alert) {
            throw new \InvalidArgumentException('Alert not found');
        }

        $this->monitoringAggregate->resolveAlert($alert, $resolverId);
        $this->monitoringRepository->updateAlertStatus($alert);
    }

    public function getActiveAlerts(): Collection
    {
        return $this->monitoringRepository->getActiveAlerts();
    }

    public function getSystemHealthMetrics(): array
    {
        return $this->monitoringRepository->getSystemHealthMetrics();
    }

    public function getAlertStatistics(): array
    {
        return $this->monitoringRepository->getAlertStatistics();
    }

    public function getMetricsByType(string $type): Collection
    {
        return $this->monitoringRepository->getMetricsByType(new MetricType($type));
    }
}