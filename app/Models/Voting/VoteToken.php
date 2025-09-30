<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Election\Election;

class VoteToken extends Model
{
    protected $fillable = [
        'user_id',
        'election_id',
        'token_hash',
        'is_used',
        'used_at',
        'vote_receipt_hash',
        'is_revoked',
        'revoked_at',
        'revoked_by',
        'issued_at',
        'issued_by',
        'reassigned_at',
        'reassigned_by',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'is_revoked' => 'boolean',
        'used_at' => 'datetime',
        'revoked_at' => 'datetime',
        'issued_at' => 'datetime',
        'reassigned_at' => 'datetime',
    ];

    /**
     * Get the user that owns the vote token
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the election that the token is for
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Check if the token is still valid
     */
    public function isValid(): bool
    {
        return !$this->is_used && !$this->is_revoked;
    }

    /**
     * Get the admin who issued the token
     */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get the admin who revoked the token
     */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Get the admin who reassigned the token
     */
    public function reassignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reassigned_by');
    }

    /**
     * Mark the token as used
     */
    public function markAsUsed(?string $receiptHash = null): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
            'vote_receipt_hash' => $receiptHash,
        ]);
    }

    /**
     * Generate a secure token hash
     */
    public static function generateSecureTokenHash(User $user, Election $election): string
    {
        $data = $user->getKey() . $election->getKey() . now()->timestamp . \Illuminate\Support\Str::random(32);
        return hash('sha256', $data . config('app.key'));
    }
}