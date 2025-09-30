<?php

namespace App\Domains\Analytics\ValueObjects;

class ReportStatus
{
    private string $status;

    private const VALID_STATUSES = ['draft', 'generating', 'completed', 'failed'];

    public function __construct(string $status)
    {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException('Invalid report status: ' . $status);
        }
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function equals(ReportStatus $other): bool
    {
        return $this->status === $other->status;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function canTransitionTo(ReportStatus $newStatus): bool
    {
        $transitions = [
            'draft' => ['generating'],
            'generating' => ['completed', 'failed'],
            'completed' => [],
            'failed' => ['draft']
        ];

        return in_array($newStatus->getStatus(), $transitions[$this->status] ?? []);
    }

    public function __toString(): string
    {
        return $this->status;
    }
}