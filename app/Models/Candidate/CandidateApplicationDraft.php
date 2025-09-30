<?php

namespace App\Models\Candidate;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Election\Election;

class CandidateApplicationDraft extends Model
{
    protected $fillable = [
        'user_id',
        'election_id',
        'form_data',
        'current_step',
        'uploaded_files',
    ];

    protected $casts = [
        'form_data' => 'array',
        'uploaded_files' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function election()
    {
        return $this->belongsTo(Election::class);
    }
}