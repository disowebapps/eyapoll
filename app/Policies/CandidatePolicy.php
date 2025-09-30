<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Candidate\Candidate;

class CandidatePolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission('manage_users') || $admin->hasPermission('manage_elections');
    }

    public function view(Admin $admin, Candidate $candidate): bool
    {
        return $admin->hasPermission('manage_users') || $admin->hasPermission('manage_elections');
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermission('manage_users');
    }

    public function update(Admin $admin, Candidate $candidate): bool
    {
        return $admin->hasPermission('manage_users') || $admin->hasPermission('manage_elections');
    }

    public function delete(Admin $admin, Candidate $candidate): bool
    {
        return $admin->hasPermission('manage_users');
    }
    
    public function becomeCandidate($user): bool
    {
        // User must be verified (approved status) to become a candidate
        return $user && $user->status === 'approved';
    }
}