<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Election\Election;

class VoteRecord extends Model
{
    protected $fillable = [
        'election_id', 'voter_hash', 'encrypted_selections', 'receipt_hash',
        'chain_hash', 'previous_hash', 'cast_at', 'verification_data'
    ];

    protected $casts = [
        'cast_at' => 'datetime',
        'verification_data' => 'json'
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function verifyChainIntegrity(): bool
    {
        if (!$this->previous_hash) {
            return true; // First vote in chain
        }

        $previousVote = static::where('chain_hash', $this->previous_hash)->first();
        return $previousVote !== null;
    }

    public function generateReceiptHash(): string
    {
        return hash('sha256', 
            $this->voter_hash . 
            $this->election_id . 
            $this->cast_at->timestamp . 
            config('app.key')
        );
    }

    public static function getLastChainHash(int $electionId): ?string
    {
        return static::where('election_id', $electionId)
            ->orderBy('cast_at', 'desc')
            ->value('chain_hash');
    }
}