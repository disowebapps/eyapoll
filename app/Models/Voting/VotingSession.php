<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Model;
use App\Services\Cryptographic\CryptographicService;

class VotingSession extends Model
{
    protected $fillable = [
        'session_id',
        'voter_hash',
        'election_id',
        'selections',
        'current_position_index',
        'progress',
        'last_activity_at'
    ];

    protected $casts = [
        'last_activity_at' => 'datetime'
    ];

    /**
     * Encrypt sensitive data before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($session) {
            $cryptoService = app(CryptographicService::class);

            if ($session->selections && is_array($session->selections)) {
                $session->selections = $cryptoService->encryptData($session->selections);
            }

            if ($session->progress && is_array($session->progress)) {
                $session->progress = $cryptoService->encryptData($session->progress);
            }
        });
    }

    /**
     * Decrypt selections when accessed
     */
    public function getSelectionsAttribute($value)
    {
        if (!$value) return [];
        if (is_array($value)) return $value;

        try {
            $cryptoService = app(CryptographicService::class);
            return $cryptoService->decryptData($value) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Decrypt progress when accessed
     */
    public function getProgressAttribute($value)
    {
        if (!$value) return [];
        if (is_array($value)) return $value;

        try {
            $cryptoService = app(CryptographicService::class);
            return $cryptoService->decryptData($value) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }



    public static function createOrUpdate(string $sessionId, string $voterHash, int $electionId, array $data): self
    {
        return self::updateOrCreate(
            ['session_id' => $sessionId],
            array_merge($data, [
                'voter_hash' => $voterHash,
                'election_id' => $electionId,
                'last_activity_at' => now()
            ])
        );
    }
}