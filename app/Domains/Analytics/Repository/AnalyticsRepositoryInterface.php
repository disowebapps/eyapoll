<?php

namespace App\Domains\Analytics\Repository;

use App\Domains\Analytics\Entities\AnalyticsReport;
use App\Domains\Analytics\ValueObjects\ReportType;
use App\Domains\Analytics\ValueObjects\ReportStatus;
use Illuminate\Support\Collection;

interface AnalyticsRepositoryInterface
{
    public function saveReport(AnalyticsReport $report): void;
    public function findReportById(int $id): ?AnalyticsReport;
    public function getReportsByType(ReportType $type): Collection;
    public function getReportsByStatus(ReportStatus $status): Collection;
    public function getReportsByUser(int $userId): Collection;
    public function getActiveReports(): Collection;
    public function getCompletedReports(int $limit = 100): Collection;
    public function getFailedReports(): Collection;
    public function updateReportStatus(AnalyticsReport $report): void;
    public function deleteReport(int $reportId): void;
    public function getReportStatistics(): array;
}