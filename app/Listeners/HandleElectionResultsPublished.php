<?php

namespace App\Listeners;

use App\Events\ElectionResultsPublished;
use App\Jobs\ResumeVoterRegistration;

class HandleElectionResultsPublished
{
    public function handle(ElectionResultsPublished $event): void
    {
        ResumeVoterRegistration::dispatch($event->election);
    }
}