<?php

namespace App\Domains\Analytics\Aggregates;

use App\Domains\Analytics\Entities\AnalyticsReport;
use App\Domains\Analytics\ValueObjects\ReportType;
use App\Domains\Analytics\ValueObjects\ReportStatus;
use App\Domains\Analytics\DomainEvents\ReportCreatedEvent;
use App\Domains\Analytics\DomainEvents\ReportCompletedEvent;
use App\Domains\Analytics\DomainEvents\ReportFailedEvent;
use Illuminate\Support\Collection;

class AnalyticsAggregate
{
    private Collection $reports;
    private int $maxConcurrentReports = 5;

    public function __construct()
    {
        $this->reports = collect();
    }

    public function createReport(ReportType $type, string $title, array $parameters, int $userId): AnalyticsReport
    {
        $this->ensureCanCreateReport();

        $report = new AnalyticsReport($type, $title, $parameters, $userId);

        $this->reports->push($report);

        // Raise domain event
        event(new ReportCreatedEvent($report));

        return $report;
    }

    public function startReportGeneration(AnalyticsReport $report): void
    {
        $report->startGeneration();
    }

    public function completeReportGeneration(AnalyticsReport $report, array $data): void
    {
        $report->completeGeneration($data);

        // Raise domain event
        event(new ReportCompletedEvent($report));
    }

    public function failReportGeneration(AnalyticsReport $report): void
    {
        $report->failGeneration();

        // Raise domain event
        event(new ReportFailedEvent($report));
    }

    public function regenerateReport(AnalyticsReport $report): void
    {
        if (!$report->canBeRegenerated()) {
            throw new \DomainException('Report cannot be regenerated');
        }

        $report->regenerate();
    }

    public function getReportsByType(ReportType $type): Collection
    {
        return $this->reports->filter(fn(AnalyticsReport $report) => $report->getType()->equals($type));
    }

    public function getReportsByStatus(ReportStatus $status): Collection
    {
        return $this->reports->filter(fn(AnalyticsReport $report) => $report->getStatus()->equals($status));
    }

    public function getActiveReports(): Collection
    {
        return $this->reports->filter(fn(AnalyticsReport $report) =>
            $report->getStatus()->equals(new ReportStatus('generating'))
        );
    }

    public function getCompletedReports(): Collection
    {
        return $this->reports->filter(fn(AnalyticsReport $report) =>
            $report->getStatus()->equals(new ReportStatus('completed'))
        );
    }

    public function getReportsByUser(int $userId): Collection
    {
        return $this->reports->filter(fn(AnalyticsReport $report) => $report->getGeneratedBy() === $userId);
    }

    private function ensureCanCreateReport(): void
    {
        $activeCount = $this->getActiveReports()->count();
        if ($activeCount >= $this->maxConcurrentReports) {
            throw new \DomainException('Maximum number of concurrent reports reached');
        }
    }
}