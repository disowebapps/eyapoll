<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use App\Models\Election\Election;


class ElectionPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission('manage_elections') || $admin->hasPermission('view_reports');
    }

    public function view($user, Election $election): bool
    {
        // Admin access
        if ($user instanceof Admin) {
            return $user->hasPermission('manage_elections') || $user->hasPermission('view_reports');
        }
        
        // Voter access (for receipt viewing)
        if ($user instanceof User) {
            return true; // Voters can view elections they have access to
        }
        
        return false;
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermission('manage_elections');
    }

    public function update(Admin $admin, Election $election): bool
    {
        return $admin->hasPermission('manage_elections') && $election->canBeEdited();
    }

    public function delete(Admin $admin, Election $election): bool
    {
        return $admin->hasPermission('manage_elections') && $election->canBeCancelled();
    }

    public function vote(?User $user, Election $election): bool
    {
        if (!$user) {
            return false;
        }


        return $user->status->value === 'approved' &&
               $election->status->value === 'active' &&
               $election->starts_at <= now() &&
               $election->ends_at >= now();
    }

    public function viewResults(?User $user, Election $election): bool
    {
        // Allow viewing results if published, regardless of user status
        return $election->results_published;
    }

    public function apply($user, Election $election): bool
    {
        \Log::info('ElectionPolicy::apply called', [
            'user_type' => $user ? get_class($user) : 'null',
            'user_id' => $user?->id,
            'election_id' => $election->id,
            'election_status' => $election->status->value,
            'can_accept_applications' => $election->canAcceptCandidateApplications()
        ]);
        
        // Admin access - always allow
        if ($user instanceof Admin) {
            $hasPermission = $user->hasPermission('manage_users');
            $canAccept = $election->canAcceptCandidateApplications();
            
            \Log::info('Admin apply check', [
                'has_permission' => $hasPermission,
                'can_accept_applications' => $canAccept,
                'result' => $hasPermission && $canAccept
            ]);
            
            return $hasPermission && $canAccept;
        }
        
        // User access - check approval and dates
        if ($user instanceof User) {
            if (!$user->isApproved()) {
                \Log::info('User not approved', ['user_id' => $user->id]);
                return false;
            }
            
            $now = now();
            
            // If no application dates set, deny by default
            if (!$election->candidate_register_starts || !$election->candidate_register_ends) {
                \Log::info('Application dates not set', [
                    'starts' => $election->candidate_register_starts,
                    'ends' => $election->candidate_register_ends
                ]);
                return false;
            }
            
            // Check application period dates - this is final
            if ($now->lt($election->candidate_register_starts) || $now->gt($election->candidate_register_ends)) {
                \Log::info('Outside application period', [
                    'now' => $now,
                    'starts' => $election->candidate_register_starts,
                    'ends' => $election->candidate_register_ends
                ]);
                return false;
            }
            
            // Only check election status if within date range
            $result = $election->canAcceptCandidateApplications();
            \Log::info('User apply result', ['result' => $result]);
            return $result;
        }
        
        \Log::info('Unknown user type or null user');
        return false;
    }
    
    public function getApplicationMessage(Election $election): string
    {
        if (!$election->candidate_register_starts || !$election->candidate_register_ends) {
            return 'Application dates not configured for this election';
        }
        
        $now = now();
        
        if ($now->lt($election->candidate_register_starts)) {
            return 'Applications open on ' . $election->candidate_register_starts->format('M j, Y g:i A');
        }
        
        if ($now->gt($election->candidate_register_ends)) {
            return 'Application deadline passed on ' . $election->candidate_register_ends->format('M j, Y g:i A');
        }
        
        return 'Applications are currently open';
    }
}