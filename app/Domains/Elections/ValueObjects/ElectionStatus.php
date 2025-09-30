<?php

namespace App\Domains\Elections\ValueObjects;

class ElectionStatus
{
    private string $status;

    private const VALID_STATUSES = ['draft', 'announced', 'active', 'completed', 'cancelled'];

    public function __construct(string $status)
    {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException('Invalid election status: ' . $status);
        }
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function equals(ElectionStatus $other): bool
    {
        return $this->status === $other->status;
    }

    public function canTransitionTo(ElectionStatus $newStatus): bool
    {
        $transitions = [
            'draft' => ['announced', 'cancelled'],
            'announced' => ['active', 'cancelled'],
            'active' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => []
        ];

        return in_array($newStatus->getStatus(), $transitions[$this->status] ?? []);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function __toString(): string
    {
        return $this->status;
    }
}