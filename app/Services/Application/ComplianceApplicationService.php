<?php

namespace App\Services;

use App\Domains\Compliance\Repository\ComplianceRepositoryInterface;
use Illuminate\Support\Collection;

class ComplianceApplicationService
{
    private ComplianceRepositoryInterface $complianceRepository;

    public function __construct(ComplianceRepositoryInterface $complianceRepository)
    {
        $this->complianceRepository = $complianceRepository;
    }

    public function checkComplianceStatus(): array
    {
        return $this->complianceRepository->checkComplianceStatus();
    }

    public function logComplianceViolation(string $violation, array $context = []): void
    {
        $this->complianceRepository->logComplianceViolation($violation, $context);
    }
}