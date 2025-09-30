<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsensusApproval extends Model
{
    protected $fillable = [
        'requested_by',
        'entity_type',
        'entity_id',
        'action',
        'status',
    ];

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}