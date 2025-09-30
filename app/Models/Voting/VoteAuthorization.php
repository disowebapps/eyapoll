<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Election\Election;
use Carbon\Carbon;

class VoteAuthorization extends Model
{
    protected $fillable = [
        'voter_hash', 'election_id', 'auth_token', 'expires_at', 'is_used',
        'extension_count', 'last_activity_at', 'initial_timeout_minutes', 'eligibility_snapshot'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'is_used' => 'boolean',
        'eligibility_snapshot' => 'json'
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function isValid(): bool
    {
        return !$this->is_used && 
               !$this->hasExpired() &&
               $this->verifyIntegrity();
    }

    public function hasExpired(): bool
    {
        return $this->expires_at <= now();
    }

    public function timeLeft(): int
    {
        return max(0, $this->expires_at->diffInSeconds(now()));
    }

    public function extendTimeout(int $minutes): bool
    {
        if ($this->extension_count >= 3) {
            return false;
        }

        $this->update([
            'expires_at' => now()->addMinutes($minutes),
            'extension_count' => $this->extension_count + 1,
            'last_activity_at' => now()
        ]);

        return true;
    }

    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    public function isActivelyVoting(): bool
    {
        return $this->last_activity_at && 
               $this->last_activity_at->diffInMinutes(now()) < 2;
    }

    public function markAsUsed(): void
    {
        $this->update(['is_used' => true]);
    }

    public function verifyIntegrity(): bool
    {
        $expectedHash = hash('sha256', $this->voter_hash . $this->election_id . config('app.key'));
        return hash_equals($expectedHash, substr($this->auth_token, 0, 64));
    }

    public static function generateSecureToken(string $voterHash, int $electionId): string
    {
        $hash = hash('sha256', $voterHash . $electionId . config('app.key'));
        $random = bin2hex(random_bytes(32));
        return $hash . $random;
    }
}