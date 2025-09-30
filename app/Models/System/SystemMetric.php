<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class SystemMetric extends Model
{
    protected $fillable = [
        'metric_name',
        'value',
        'unit',
        'metadata',
        'recorded_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'value' => 'decimal:2',
        'recorded_at' => 'datetime'
    ];

    public static function record(string $name, float $value, ?string $unit = null, array $metadata = []): self
    {
        return self::create([
            'metric_name' => $name,
            'value' => $value,
            'unit' => $unit,
            'metadata' => $metadata,
            'recorded_at' => now()
        ]);
    }

    public static function latest(string $name): ?float
    {
        return self::where('metric_name', $name)
            ->latest('recorded_at')
            ->value('value');
    }

    public function scopeByName($query, string $name)
    {
        return $query->where('metric_name', $name);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('recorded_at', '>=', now()->subHours($hours));
    }
}