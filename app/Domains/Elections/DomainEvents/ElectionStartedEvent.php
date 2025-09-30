<?php

namespace App\Domains\Elections\DomainEvents;

use App\Domains\Elections\Entities\Election;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ElectionStartedEvent
{
    use Dispatchable, SerializesModels;

    public Election $election;

    public function __construct(Election $election)
    {
        $this->election = $election;
    }
}