<?php

namespace App\Domains\Elections\ValueObjects;

class PositionType
{
    private string $type;

    private const VALID_TYPES = ['president', 'governor', 'senator', 'representative', 'chairman', 'councilor'];

    public function __construct(string $type)
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw new \InvalidArgumentException('Invalid position type: ' . $type);
        }
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function equals(PositionType $other): bool
    {
        return $this->type === $other->type;
    }

    public function isExecutive(): bool
    {
        return in_array($this->type, ['president', 'governor', 'chairman']);
    }

    public function isLegislative(): bool
    {
        return in_array($this->type, ['senator', 'representative', 'councilor']);
    }

    public function __toString(): string
    {
        return $this->type;
    }
}