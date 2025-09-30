<?php

namespace App\Services;

use App\Domains\Security\Aggregates\SecurityAggregate;
use App\Domains\Security\Repository\SecurityRepositoryInterface;
use App\Domains\Security\Entities\SecurityEvent;
use App\Domains\Security\ValueObjects\EventType;
use Illuminate\Support\Collection;

class SecurityApplicationService
{
    private SecurityAggregate $securityAggregate;
    private SecurityRepositoryInterface $securityRepository;

    public function __construct(SecurityRepositoryInterface $securityRepository)
    {
        $this->securityRepository = $securityRepository;
        $this->securityAggregate = new SecurityAggregate();
    }

    public function logSecurityEvent(string $eventType, string $severity, array $context = []): SecurityEvent
    {
        $event = $this->securityAggregate->logSecurityEvent(
            new EventType($eventType),
            $severity,
            $context
        );

        $this->securityRepository->saveSecurityEvent($event);

        return $event;
    }

    public function getSecurityEvents(): Collection
    {
        return $this->securityAggregate->getSecurityEvents();
    }

    public function getSecurityIncidents(): Collection
    {
        return $this->securityAggregate->getIncidents();
    }

    public function getRecentSecurityEvents(int $limit = 100): Collection
    {
        return $this->securityRepository->getRecentSecurityEvents($limit);
    }

    public function getSecurityEventsBySeverity(string $severity): Collection
    {
        return $this->securityRepository->getSecurityEventsBySeverity($severity);
    }
}