<?php

namespace App\Policies;

use App\Models\ElectionAppeal;
use App\Models\User;
use App\Models\Admin;

class AppealPolicy
{
    /**
     * Determine whether the user can view any appeals
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own appeals
    }

    /**
     * Determine whether the user can view the appeal
     */
    public function view(User $user, ElectionAppeal $appeal): bool
    {
        return $appeal->appellant_id === $user->id;
    }

    /**
     * Determine whether the user can create appeals
     */
    public function create(User $user): bool
    {
        // User must be approved and verified to submit appeals
        return $user->status->value === 'approved' && $user->is_identity_verified;
    }

    /**
     * Determine whether the user can update the appeal
     */
    public function update(User $user, ElectionAppeal $appeal): bool
    {
        // User can only update their own appeals that are still in submitted status
        return $appeal->appellant_id === $user->id &&
               $appeal->status->value === 'submitted';
    }

    /**
     * Determine whether the user can delete the appeal
     */
    public function delete(User $user, ElectionAppeal $appeal): bool
    {
        // Users can only delete their own appeals that are still in submitted status
        return $appeal->appellant_id === $user->id &&
               $appeal->status->value === 'submitted';
    }

    /**
     * Determine whether the admin can view any appeals
     */
    public function viewAnyAdmin(Admin $admin): bool
    {
        return $admin->hasPermission('manage_elections') ||
               $admin->hasPermission('manage_users') ||
               $admin->hasPermission('view_appeals');
    }

    /**
     * Determine whether the admin can view the appeal
     */
    public function viewAdmin(Admin $admin, ElectionAppeal $appeal): bool
    {
        return $admin->hasPermission('manage_elections') ||
               $admin->hasPermission('manage_users') ||
               $admin->hasPermission('view_appeals');
    }

    /**
     * Determine whether the admin can assign appeals
     */
    public function assign(Admin $admin, ElectionAppeal $appeal): bool
    {
        return $admin->hasPermission('manage_elections') ||
               $admin->hasPermission('manage_appeals');
    }

    /**
     * Determine whether the admin can update appeal status
     */
    public function updateStatus(Admin $admin, ElectionAppeal $appeal): bool
    {
        // Admin can update if they have general permissions or are assigned to this appeal
        return $admin->hasPermission('manage_elections') ||
               $admin->hasPermission('manage_appeals') ||
               ($admin->hasPermission('review_appeals') && $appeal->assigned_to === $admin->id);
    }

    /**
     * Determine whether the admin can escalate appeals
     */
    public function escalate(Admin $admin, ElectionAppeal $appeal): bool
    {
        return $admin->hasPermission('manage_elections') ||
               $admin->hasPermission('manage_appeals') ||
               $admin->hasPermission('escalate_appeals');
    }

    /**
     * Determine whether the admin can review appeal documents
     */
    public function reviewDocuments(Admin $admin, ElectionAppeal $appeal): bool
    {
        return $admin->hasPermission('manage_elections') ||
               $admin->hasPermission('manage_appeals') ||
               ($admin->hasPermission('review_appeals') && $appeal->assigned_to === $admin->id);
    }

    /**
     * Determine whether the admin can perform bulk operations
     */
    public function bulkOperations(Admin $admin): bool
    {
        return $admin->hasPermission('manage_elections') ||
               $admin->hasPermission('manage_appeals');
    }
}