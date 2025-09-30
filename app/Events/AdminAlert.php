<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminAlert
{
    use Dispatchable, SerializesModels;

    public $alert;

    public function __construct($alert)
    {
        $this->alert = $alert;
    }
}