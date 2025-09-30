<?php

namespace App\Repositories;

use App\Domains\Security\Repository\SecurityRepositoryInterface;
use App\Domains\Security\Entities\SecurityEvent;
use App\Models\System\SecurityEvent as SecurityEventModel;
use Illuminate\Support\Collection;

class SecurityRepository implements SecurityRepositoryInterface
{
    public function saveSecurityEvent(SecurityEvent $event): void
    {
        $model = SecurityEventModel::updateOrCreate(
            ['id' => $event->getId()],
            [
                'event_type' => $event->getType()->getType(),
                'severity' => $event->getSeverity(),
                'source_ip' => $event->getContext()['source_ip'] ?? null,
                'user_agent' => $event->getContext()['user_agent'] ?? null,
                'user_id' => $event->getContext()['user_id'] ?? null,
                'context' => $event->getContext(),
            ]
        );

        // Update the domain entity with the actual ID
        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($event, $model->id);
    }

    public function findSecurityEventById(int $id): ?SecurityEvent
    {
        $model = SecurityEventModel::find($id);
        return $model ? $this->modelToDomainEvent($model) : null;
    }

    public function getSecurityEventsByType(string $eventType): Collection
    {
        return SecurityEventModel::where('event_type', $eventType)
            ->get()
            ->map(fn($model) => $this->modelToDomainEvent($model));
    }

    public function getRecentSecurityEvents(int $limit = 100): Collection
    {
        return SecurityEventModel::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($model) => $this->modelToDomainEvent($model));
    }

    public function getSecurityIncidents(): Collection
    {
        return SecurityEventModel::whereIn('event_type', ['intrusion_attempt', 'suspicious_activity', 'breach'])
            ->get()
            ->map(fn($model) => $this->modelToDomainEvent($model));
    }

    public function getSecurityEventsBySeverity(string $severity): Collection
    {
        return SecurityEventModel::where('severity', $severity)
            ->get()
            ->map(fn($model) => $this->modelToDomainEvent($model));
    }

    private function modelToDomainEvent(SecurityEventModel $model): SecurityEvent
    {
        $event = new SecurityEvent(
            new \App\Domains\Security\ValueObjects\EventType($model->event_type),
            $model->severity,
            $model->context ?? []
        );

        // Set the ID and occurred_at
        $reflection = new \ReflectionClass($event);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($event, $model->id);

        $occurredAtProperty = $reflection->getProperty('occurredAt');
        $occurredAtProperty->setAccessible(true);
        $occurredAtProperty->setValue($event, $model->created_at);

        return $event;
    }
}