<?php

namespace App\Models\Candidate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;

class CandidateActionHistory extends Model
{
    protected $table = 'candidate_action_history';
    
    protected $fillable = [
        'candidate_id',
        'admin_id', 
        'action',
        'reason',
        'previous_status',
        'new_status',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}