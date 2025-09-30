<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can update their name
     */
    public function updateName(User $user): bool
    {
        return !$user->is_identity_verified;
    }
}