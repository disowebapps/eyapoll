<?php

namespace App\Models\Election;

use Illuminate\Database\Eloquent\Model;

class ElectionSnapshot extends Model
{
    protected $fillable = [
        'election_id',
        'status',
        'starts_at',
        'ends_at',
        'snapshot_at',
        'hash'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'snapshot_at' => 'datetime'
    ];

    public static function createSnapshot(Election $election): self
    {
        $data = [
            'election_id' => $election->id,
            'status' => $election->status->value,
            'starts_at' => $election->starts_at,
            'ends_at' => $election->ends_at,
            'snapshot_at' => now()
        ];
        
        $data['hash'] = hash('sha256', json_encode($data));
        
        return self::create($data);
    }
}