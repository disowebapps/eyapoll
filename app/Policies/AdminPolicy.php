<?php

namespace App\Policies;

use App\Models\Admin;

class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission('manage_users') || $admin->hasPermission('system_settings');
    }

    public function view(Admin $admin, Admin $targetAdmin): bool
    {
        return $admin->hasPermission('manage_users') || $admin->id === $targetAdmin->id;
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermission('system_settings');
    }

    public function update(Admin $admin, Admin $targetAdmin): bool
    {
        return $admin->hasPermission('system_settings') || 
               ($admin->hasPermission('manage_users') && $admin->id !== $targetAdmin->id);
    }

    public function delete(Admin $admin, Admin $targetAdmin): bool
    {
        return $admin->hasPermission('system_settings') && $admin->id !== $targetAdmin->id;
    }
}