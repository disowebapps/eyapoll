<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class MFASetting extends Model
{
    protected $fillable = [
        'user_id',
        'secret',
        'enabled',
        'backup_codes',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'backup_codes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setSecretAttribute($value)
    {
        $this->attributes['secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
}
