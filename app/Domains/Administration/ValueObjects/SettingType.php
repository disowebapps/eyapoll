<?php

namespace App\Domains\Administration\ValueObjects;

class SettingType
{
    private string $type;

    private const VALID_TYPES = ['string', 'integer', 'boolean', 'float', 'json', 'array'];

    public function __construct(string $type)
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw new \InvalidArgumentException('Invalid setting type: ' . $type);
        }
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function equals(SettingType $other): bool
    {
        return $this->type === $other->type;
    }

    public function validateValue($value): bool
    {
        switch ($this->type) {
            case 'string':
                return is_string($value);
            case 'integer':
                return is_int($value);
            case 'boolean':
                return is_bool($value);
            case 'float':
                return is_float($value) || is_int($value);
            case 'json':
                return is_string($value) && json_decode($value) !== null;
            case 'array':
                return is_array($value);
            default:
                return false;
        }
    }

    public function castValue($value)
    {
        switch ($this->type) {
            case 'string':
                return (string) $value;
            case 'integer':
                return (int) $value;
            case 'boolean':
                return (bool) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return is_string($value) ? $value : json_encode($value);
            case 'array':
                return is_array($value) ? $value : (array) $value;
            default:
                return $value;
        }
    }

    public function __toString(): string
    {
        return $this->type;
    }
}