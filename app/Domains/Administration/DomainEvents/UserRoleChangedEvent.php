<?php

namespace App\Domains\Administration\DomainEvents;

use App\Domains\Administration\Entities\SystemUser;
use App\Domains\Administration\ValueObjects\UserRole;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRoleChangedEvent
{
    use Dispatchable, SerializesModels;

    public SystemUser $user;
    public UserRole $oldRole;
    public UserRole $newRole;

    public function __construct(SystemUser $user, UserRole $oldRole, UserRole $newRole)
    {
        $this->user = $user;
        $this->oldRole = $oldRole;
        $this->newRole = $newRole;
    }
}