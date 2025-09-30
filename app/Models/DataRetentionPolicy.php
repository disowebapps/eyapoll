<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataRetentionPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_name',
        'policy_type',
        'retention_days',
        'description',
        'auto_delete',
        'conditions',
        'is_active'
    ];

    protected $casts = [
        'auto_delete' => 'boolean',
        'is_active' => 'boolean',
        'conditions' => 'array'
    ];

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('policy_type', $type);
    }

    public function scopeAutoDelete($query)
    {
        return $query->where('auto_delete', true);
    }
}