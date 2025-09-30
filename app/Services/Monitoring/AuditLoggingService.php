<?php

namespace App\Services\Monitoring;

use App\Models\AuditLog;
use App\Models\User;
use App\Enums\Auth\UserRole;
use Illuminate\Http\Request;

class AuditLoggingService
{
    public function logAction(
        User $user,
        string $action,
        array $details = [],
        ?Request $request = null
    ): void {
        AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'details' => $details,
            'ip_address' => $request ? $request->ip() : request()->ip(),
            'user_agent' => $request ? $request->userAgent() : request()->userAgent(),
        ]);
    }

    public function logAdminAction(
        User $admin,
        string $action,
        array $details = [],
        ?Request $request = null
    ): void {
        // Ensure only admins can log admin actions
        if (!in_array($admin->role, UserRole::getAdminRoles())) {
            return;
        }

        $this->logAction($admin, "admin.{$action}", $details, $request);
    }

    public function logKycReview(
        User $admin,
        User $targetUser,
        string $decision,
        array $additionalDetails = []
    ): void {
        $this->logAdminAction($admin, 'kyc_review', array_merge([
            'target_user_id' => $targetUser->id,
            'target_user_email' => $targetUser->email,
            'decision' => $decision,
        ], $additionalDetails));
    }

    public function logDocumentReview(
        User $admin,
        int $documentId,
        string $decision,
        array $additionalDetails = []
    ): void {
        $this->logAdminAction($admin, 'document_review', array_merge([
            'document_id' => $documentId,
            'decision' => $decision,
        ], $additionalDetails));
    }

    public function logUserApproval(
        User $admin,
        User $targetUser,
        array $additionalDetails = []
    ): void {
        $this->logAdminAction($admin, 'user_approval', array_merge([
            'target_user_id' => $targetUser->id,
            'target_user_email' => $targetUser->email,
        ], $additionalDetails));
    }

    public function logMfaAction(
        User $user,
        string $action,
        array $details = []
    ): void {
        $this->logAction($user, "mfa.{$action}", $details);
    }
}