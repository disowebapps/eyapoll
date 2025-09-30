<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IpBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'reason',
        'blocked_by',
        'blocked_until',
        'is_permanent',
        'violation_count',
        'metadata',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
        'is_permanent' => 'boolean',
        'violation_count' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('is_permanent', true)
              ->orWhere('blocked_until', '>', Carbon::now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('is_permanent', false)
                    ->where('blocked_until', '<=', Carbon::now());
    }

    public function scopePermanent($query)
    {
        return $query->where('is_permanent', true);
    }

    public function scopeTemporary($query)
    {
        return $query->where('is_permanent', false);
    }

    /**
     * Helper methods
     */
    public static function isBlocked(string $ipAddress): bool
    {
        return static::active()->where('ip_address', $ipAddress)->exists();
    }

    public static function blockIp(string $ipAddress, string $reason, ?string $blockedBy = null, ?int $durationMinutes = null, array $metadata = []): self
    {
        $blockedUntil = $durationMinutes ? Carbon::now()->addMinutes($durationMinutes) : null;
        $isPermanent = $durationMinutes === null;

        return static::updateOrCreate(
            ['ip_address' => $ipAddress],
            [
                'reason' => $reason,
                'blocked_by' => $blockedBy,
                'blocked_until' => $blockedUntil,
                'is_permanent' => $isPermanent,
                'violation_count' => DB::raw('violation_count + 1'),
                'metadata' => $metadata,
            ]
        );
    }

    public static function unblockIp(string $ipAddress): bool
    {
        return static::where('ip_address', $ipAddress)->delete() > 0;
    }

    public function isActive(): bool
    {
        return $this->is_permanent || ($this->blocked_until && $this->blocked_until->isFuture());
    }

    public function getRemainingTime(): ?int
    {
        if ($this->is_permanent) {
            return null;
        }

        return $this->blocked_until ? Carbon::now()->diffInMinutes($this->blocked_until, false) : 0;
    }

    public static function cleanupExpiredBlocks(): int
    {
        return static::expired()->delete();
    }

    public static function getBlockedIps(): \Illuminate\Support\Collection
    {
        return static::active()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
