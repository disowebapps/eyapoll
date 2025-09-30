t<?php

namespace App\Events\Candidate;

use App\Models\Candidate\Candidate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CandidateApplied
{
    use Dispatchable, SerializesModels;

    public Candidate $candidate;

    /**
     * Create a new event instance.
     */
    public function __construct(Candidate $candidate)
    {
        $this->candidate = $candidate;
    }
}