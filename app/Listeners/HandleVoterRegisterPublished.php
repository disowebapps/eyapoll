<?php

namespace App\Listeners;

use App\Events\VoterRegisterPublished;
use App\Jobs\PauseVoterRegistration;

class HandleVoterRegisterPublished
{
    public function handle(VoterRegisterPublished $event): void
    {
        PauseVoterRegistration::dispatch($event->election);
    }
}