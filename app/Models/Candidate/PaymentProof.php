<?php

namespace App\Models\Candidate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = [
        'candidate_id',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'reference_number',
        'payment_method',
        'amount_paid',
        'payment_date'
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date'
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}