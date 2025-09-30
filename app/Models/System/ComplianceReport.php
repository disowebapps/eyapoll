<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ComplianceReport extends Model
{
    protected $fillable = [
        'report_type',
        'data',
        'status',
        'generated_at'
    ];

    protected $casts = [
        'data' => 'array',
        'generated_at' => 'datetime'
    ];

    public static function generate(string $type, array $data): self
    {
        return self::create([
            'report_type' => $type,
            'data' => $data,
            'status' => 'generated',
            'generated_at' => now()
        ]);
    }

    public function isCompliant(): bool
    {
        return $this->status === 'compliant';
    }

    public function hasViolations(): bool
    {
        return isset($this->data['violations']) && !empty($this->data['violations']);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('generated_at', '>=', now()->subDays($days));
    }
}