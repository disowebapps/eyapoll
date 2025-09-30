<?php

namespace App\Services;

use App\Domains\Analytics\Repository\AnalyticsRepositoryInterface;
use Illuminate\Support\Collection;

class AnalyticsApplicationService
{
    private AnalyticsRepositoryInterface $analyticsRepository;

    public function __construct(AnalyticsRepositoryInterface $analyticsRepository)
    {
        $this->analyticsRepository = $analyticsRepository;
    }

    public function getVoterParticipationStats(): array
    {
        return $this->analyticsRepository->getVoterParticipationStats();
    }

    public function getElectionResultsAnalytics(int $electionId): array
    {
        return $this->analyticsRepository->getElectionResultsAnalytics($electionId);
    }

    public function getSystemUsageMetrics(): array
    {
        return $this->analyticsRepository->getSystemUsageMetrics();
    }
}