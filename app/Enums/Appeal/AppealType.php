<?php

namespace App\Enums\Appeal;

enum AppealType: string
{
    case RESULT_IRREGULARITY = 'result_irregularity';
    case PROCEDURAL_ERROR = 'procedural_error';
    case TECHNICAL_ISSUE = 'technical_issue';
    case VOTER_FRAUD = 'voter_fraud';
    case SYSTEM_ERROR = 'system_error';

    public function label(): string
    {
        return match($this) {
            self::RESULT_IRREGULARITY => 'Result Irregularity',
            self::PROCEDURAL_ERROR => 'Procedural Error',
            self::TECHNICAL_ISSUE => 'Technical Issue',
            self::VOTER_FRAUD => 'Voter Fraud',
            self::SYSTEM_ERROR => 'System Error',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::RESULT_IRREGULARITY => 'Discrepancies or irregularities in election results',
            self::PROCEDURAL_ERROR => 'Errors in election procedures or processes',
            self::TECHNICAL_ISSUE => 'Technical problems during voting or result collation',
            self::VOTER_FRAUD => 'Allegations of voter fraud or manipulation',
            self::SYSTEM_ERROR => 'System failures or technical malfunctions',
        };
    }

    public function defaultPriority(): AppealPriority
    {
        return match($this) {
            self::VOTER_FRAUD => AppealPriority::CRITICAL,
            self::RESULT_IRREGULARITY => AppealPriority::HIGH,
            self::SYSTEM_ERROR => AppealPriority::HIGH,
            self::PROCEDURAL_ERROR => AppealPriority::MEDIUM,
            self::TECHNICAL_ISSUE => AppealPriority::MEDIUM,
        };
    }

    public function requiresEvidence(): bool
    {
        return match($this) {
            self::VOTER_FRAUD, self::RESULT_IRREGULARITY => true,
            self::PROCEDURAL_ERROR, self::TECHNICAL_ISSUE, self::SYSTEM_ERROR => false,
        };
    }

    public static function getSelectOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}