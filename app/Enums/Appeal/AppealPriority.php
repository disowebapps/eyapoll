<?php

namespace App\Enums\Appeal;

enum AppealPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::CRITICAL => 'Critical',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::LOW => 'gray',
            self::MEDIUM => 'blue',
            self::HIGH => 'orange',
            self::CRITICAL => 'red',
        };
    }

    public function responseTimeHours(): int
    {
        return match($this) {
            self::LOW => 72, // 3 days
            self::MEDIUM => 24, // 1 day
            self::HIGH => 12, // 12 hours
            self::CRITICAL => 4, // 4 hours
        };
    }

    public function escalationTimeHours(): int
    {
        return match($this) {
            self::LOW => 168, // 7 days
            self::MEDIUM => 48, // 2 days
            self::HIGH => 24, // 1 day
            self::CRITICAL => 8, // 8 hours
        };
    }

    public function canEscalateTo(self $newPriority): bool
    {
        $levels = [
            self::LOW->value => 1,
            self::MEDIUM->value => 2,
            self::HIGH->value => 3,
            self::CRITICAL->value => 4,
        ];

        return $levels[$newPriority->value] > $levels[$this->value];
    }

    public static function getSelectOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}