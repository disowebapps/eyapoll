<?php

namespace App\Domains\Analytics\ValueObjects;

class ReportType
{
    private string $type;

    private const VALID_TYPES = ['election_results', 'voter_turnout', 'candidate_performance', 'system_usage', 'security_summary'];

    public function __construct(string $type)
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw new \InvalidArgumentException('Invalid report type: ' . $type);
        }
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function equals(ReportType $other): bool
    {
        return $this->type === $other->type;
    }

    public function __toString(): string
    {
        return $this->type;
    }
}