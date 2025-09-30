<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Candidate\CandidateDocument;

class CandidateDocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        \Illuminate\Support\Facades\Log::info('CandidateDocumentPolicy::viewAny called', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'is_super_admin' => $admin->is_super_admin,
            'permissions' => $admin->permissions,
        ]);

        // Super admins have all permissions
        if ($admin->is_super_admin) {
            \Illuminate\Support\Facades\Log::info('CandidateDocumentPolicy::viewAny - Super admin access granted');
            return true;
        }

        $hasPermission = $admin->hasPermission('manage_candidates') || $admin->hasPermission('review_documents');
        \Illuminate\Support\Facades\Log::info('CandidateDocumentPolicy::viewAny - Permission check result', [
            'has_manage_candidates' => $admin->hasPermission('manage_candidates'),
            'has_review_documents' => $admin->hasPermission('review_documents'),
            'final_result' => $hasPermission,
        ]);

        return $hasPermission;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, CandidateDocument $candidateDocument): bool
    {
        // Super admins have all permissions
        if ($admin->is_super_admin) {
            return true;
        }

        return $admin->hasPermission('manage_candidates') || $admin->hasPermission('review_documents');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        return false; // Documents are created by candidates, not admins
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, CandidateDocument $candidateDocument): bool
    {
        // Super admins have all permissions
        if ($admin->is_super_admin) {
            return true;
        }

        return $admin->hasPermission('manage_candidates') || $admin->hasPermission('review_documents');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, CandidateDocument $candidateDocument): bool
    {
        // Super admins have all permissions
        if ($admin->is_super_admin) {
            return true;
        }

        return $admin->hasPermission('manage_candidates');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, CandidateDocument $candidateDocument): bool
    {
        // Super admins have all permissions
        if ($admin->is_super_admin) {
            return true;
        }

        return $admin->hasPermission('manage_candidates');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, CandidateDocument $candidateDocument): bool
    {
        // Super admins have all permissions
        if ($admin->is_super_admin) {
            return true;
        }

        return $admin->hasPermission('manage_candidates');
    }
}