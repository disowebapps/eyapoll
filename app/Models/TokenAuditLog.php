<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenAuditLog extends Model
{
    protected $fillable = [
        'token_id',
        'action',
        'metadata',
        'created_at'
    ];

    protected $casts = [
        'metadata' => 'json',
        'created_at' => 'datetime'
    ];

    public $timestamps = false;

    public function token(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Voting\VoteToken::class);
    }
}