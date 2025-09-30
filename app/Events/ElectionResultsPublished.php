<?php

namespace App\Events;

use App\Models\Election\Election;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ElectionResultsPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Election $election
    ) {}
}