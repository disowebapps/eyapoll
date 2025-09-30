<?php

namespace App\Domains\Administration\DomainEvents;

use App\Domains\Administration\Entities\SystemUser;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCreatedEvent
{
    use Dispatchable, SerializesModels;

    public SystemUser $user;

    public function __construct(SystemUser $user)
    {
        $this->user = $user;
    }
}