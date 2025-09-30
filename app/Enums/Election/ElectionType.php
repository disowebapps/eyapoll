<?php

namespace App\Enums\Election;

enum ElectionType: string
{
    case GENERAL = 'general';
    case BYE = 'bye';
    case CONSTITUTIONAL = 'constitutional';
    case OPINION = 'opinion';

    public function label(): string
    {
        return match($this) {
            self::GENERAL => 'General Election',
            self::BYE => 'Bye-Election',
            self::CONSTITUTIONAL => 'Constitutional Amendment',
            self::OPINION => 'Opinion Poll',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::GENERAL => 'Comprehensive election for leadership positions with multiple candidates and positions',
            self::BYE => 'Special election to fill vacant positions with streamlined processes',
            self::CONSTITUTIONAL => 'Referendum on proposed amendments to organizational constitution',
            self::OPINION => 'Survey to gather member sentiment on various organizational issues',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::GENERAL => 'heroicon-o-building-office',
            self::BYE => 'heroicon-o-user-plus',
            self::CONSTITUTIONAL => 'heroicon-o-scale',
            self::OPINION => 'heroicon-o-chat-bubble-left-right',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::GENERAL => 'blue',
            self::BYE => 'green',
            self::CONSTITUTIONAL => 'purple',
            self::OPINION => 'orange',
        };
    }

    public function defaultDurationHours(): int
    {
        return match($this) {
            self::GENERAL => 72, // 3 days
            self::BYE => 48, // 2 days
            self::CONSTITUTIONAL => 120, // 5 days
            self::OPINION => 24, // 1 day
        };
    }

    public function requiresCandidateApplication(): bool
    {
        return match($this) {
            self::GENERAL => true,
            self::BYE => true,
            self::CONSTITUTIONAL => false,
            self::OPINION => false,
        };
    }

    public function allowsMultiplePositions(): bool
    {
        return match($this) {
            self::GENERAL => true,
            self::BYE => false,
            self::CONSTITUTIONAL => true,
            self::OPINION => true,
        };
    }

    public function requiresConsensusToStart(): bool
    {
        return match($this) {
            self::GENERAL => true,
            self::BYE => false,
            self::CONSTITUTIONAL => true,
            self::OPINION => false,
        };
    }

    public function supportedQuestionTypes(): array
    {
        return match($this) {
            self::GENERAL => ['candidate_selection'],
            self::BYE => ['candidate_selection'],
            self::CONSTITUTIONAL => ['yes_no', 'multiple_choice', 'ranked_choice'],
            self::OPINION => ['yes_no', 'multiple_choice', 'ranked_choice', 'text_response'],
        };
    }

    public static function getSelectOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function getSelectOptionsWithDetails(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => [
                    'label' => $case->label(),
                    'description' => $case->description(),
                    'icon' => $case->icon(),
                    'color' => $case->color(),
                    'default_duration' => $case->defaultDurationHours(),
                ]
            ])
            ->toArray();
    }
}