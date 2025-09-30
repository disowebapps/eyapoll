<?php

namespace App\Domains\Elections\DomainEvents;

use App\Domains\Elections\Entities\Candidate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CandidateApprovedEvent
{
    use Dispatchable, SerializesModels;

    public Candidate $candidate;

    public function __construct(Candidate $candidate)
    {
        $this->candidate = $candidate;
    }
}