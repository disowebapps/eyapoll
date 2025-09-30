<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegulatoryReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'report_period',
        'report_date',
        'period_start',
        'period_end',
        'report_data',
        'status',
        'notes',
        'file_path',
        'generated_by',
        'submitted_at',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'report_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'report_data' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Helper methods
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function canBeSubmitted(): bool
    {
        return $this->isDraft();
    }

    public function canBeApproved(): bool
    {
        return $this->isSubmitted();
    }

    public function getReportFileUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return route('regulatory-reports.download', $this->id);
    }

    public function getFormattedPeriod(): string
    {
        if ($this->period_start && $this->period_end) {
            return $this->period_start->format('M j, Y') . ' - ' . $this->period_end->format('M j, Y');
        }

        return $this->report_date->format('M j, Y');
    }
}