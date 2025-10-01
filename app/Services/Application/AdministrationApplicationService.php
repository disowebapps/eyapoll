<?php

namespace App\Services\Application;

use App\Domains\Administration\Repository\AdministrationRepositoryInterface;
use Illuminate\Support\Collection;

class AdministrationApplicationService
{
    private AdministrationRepositoryInterface $administrationRepository;

    public function __construct(AdministrationRepositoryInterface $administrationRepository)
    {
        $this->administrationRepository = $administrationRepository;
    }

    public function getSystemSettings(): array
    {
        return $this->administrationRepository->getSystemSettings();
    }

    public function updateSystemSetting(string $key, $value): void
    {
        $this->administrationRepository->updateSystemSetting($key, $value);
    }

    public function getUserRoles(): Collection
    {
        return $this->administrationRepository->getUserRoles();
    }

    public function logAdminAction(string $action, array $context = []): void
    {
        $this->administrationRepository->logAdminAction($action, $context);
    }
}
