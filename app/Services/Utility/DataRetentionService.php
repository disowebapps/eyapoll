<?php

namespace App\Services\Utility;

use App\Models\User;
use App\Models\ComplianceLog;
use App\Models\DataRetentionPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DataRetentionService
{
    /**
     * Apply data retention policy to user
     */
    public function applyRetentionPolicy(User $user): array
    {
        try {
            $policy = $this->getApplicablePolicy($user);

            if (!$policy) {
                return [
                    'applied' => false,
                    'reason' => 'No applicable retention policy found'
                ];
            }

            $retentionDate = $this->calculateRetentionDate($user, $policy);

            $user->update([
                'data_retention_until' => $retentionDate,
                'data_retention_policy' => $policy->policy_name
            ]);

            return [
                'applied' => true,
                'policy' => $policy->policy_name,
                'retention_until' => $retentionDate,
                'days_remaining' => now()->diffInDays($retentionDate, false)
            ];

        } catch (\Exception $e) {
            Log::error('Failed to apply retention policy', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'applied' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get applicable retention policy for user
     */
    private function getApplicablePolicy(User $user): ?DataRetentionPolicy
    {
        // Get active policies ordered by specificity
        $policies = DataRetentionPolicy::where('is_active', true)
            ->orderBy('policy_type')
            ->orderBy('retention_days', 'desc') // Longer retention first
            ->get();

        foreach ($policies as $policy) {
            if ($this->policyAppliesToUser($policy, $user)) {
                return $policy;
            }
        }

        return null;
    }

    /**
     * Check if policy applies to user
     */
    private function policyAppliesToUser(DataRetentionPolicy $policy, User $user): bool
    {
        // Check conditions based on policy type
        switch ($policy->policy_type) {
            case 'user_data':
                return true; // Applies to all users

            case 'unverified_users':
                return !$user->hasVerifiedDocuments();

            case 'rejected_users':
                return $user->status->value === 'rejected';

            case 'inactive_users':
                return $user->last_login_at && $user->last_login_at->diffInDays(now()) > 365;

            case 'high_risk_users':
                return in_array($user->risk_level, ['high', 'critical']);

            default:
                return false;
        }
    }

    /**
     * Calculate retention date based on policy
     */
    private function calculateRetentionDate(User $user, DataRetentionPolicy $policy): Carbon
    {
        $baseDate = $this->getBaseDateForPolicy($user, $policy);

        return $baseDate->copy()->addDays($policy->retention_days);
    }

    /**
     * Get base date for retention calculation
     */
    private function getBaseDateForPolicy(User $user, DataRetentionPolicy $policy): Carbon
    {
        switch ($policy->policy_type) {
            case 'unverified_users':
                return $user->created_at;

            case 'rejected_users':
                return $user->updated_at; // When status changed to rejected

            case 'inactive_users':
                return $user->last_login_at ?? $user->created_at;

            case 'high_risk_users':
                return $user->risk_assessed_at ?? $user->created_at;

            default:
                return $user->created_at;
        }
    }

    /**
     * Process GDPR data export request
     */
    public function exportUserData(User $user): array
    {
        try {
            $exportData = $this->gatherUserData($user);

            // Create export file
            $filename = 'gdpr_export_' . $user->uuid . '_' . now()->format('Y-m-d_H-i-s') . '.json';
            $filePath = 'gdpr_exports/' . $filename;

            Storage::disk('private')->put($filePath, json_encode($exportData, JSON_PRETTY_PRINT));

            // Log the export
            $this->logDataAction($user, 'export', 'GDPR data export completed', [
                'file_path' => $filePath,
                'data_types' => array_keys($exportData)
            ]);

            // Update user record
            $user->update([
                'gdpr_export_requested_at' => now()
            ]);

            return [
                'exported' => true,
                'file_path' => $filePath,
                'download_url' => route('gdpr.download', $user->uuid),
                'data_types' => array_keys($exportData)
            ];

        } catch (\Exception $e) {
            Log::error('GDPR export failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            $this->logDataAction($user, 'export', 'GDPR export failed: ' . $e->getMessage());

            return [
                'exported' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process GDPR data deletion request
     */
    public function deleteUserData(User $user): array
    {
        try {
            // Check if retention period allows deletion
            if ($user->data_retention_until && $user->data_retention_until->isFuture()) {
                return [
                    'deleted' => false,
                    'reason' => 'Data retention period not expired',
                    'retention_until' => $user->data_retention_until
                ];
            }

            DB::transaction(function () use ($user) {
                // Anonymize personal data
                $this->anonymizeUserData($user);

                // Delete associated records
                $this->deleteAssociatedData($user);

                // Log the deletion
                $this->logDataAction($user, 'deletion', 'GDPR data deletion completed');

                // Update user record
                $user->update([
                    'gdpr_deletion_requested_at' => now(),
                    'status' => 'deleted' // Assuming there's a deleted status
                ]);
            });

            return [
                'deleted' => true,
                'anonymized_fields' => $this->getAnonymizedFields(),
                'deleted_records' => $this->getDeletedRecordTypes()
            ];

        } catch (\Exception $e) {
            Log::error('GDPR deletion failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            $this->logDataAction($user, 'deletion', 'GDPR deletion failed: ' . $e->getMessage());

            return [
                'deleted' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Gather all user data for export
     */
    public function gatherUserData(User $user): array
    {
        return [
            'personal_information' => [
                'uuid' => $user->uuid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'profile_image' => $user->profile_image,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ],
            'verification_data' => $user->verification_data,
            'id_documents' => $user->idDocuments->map(function ($doc) {
                return [
                    'type' => $doc->document_type,
                    'status' => $doc->status,
                    'uploaded_at' => $doc->created_at,
                    'verified_at' => $doc->verified_at
                ];
            }),
            'audit_logs' => $user->auditLogs()->take(100)->get()->map(function ($log) {
                return [
                    'action' => $log->action,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'created_at' => $log->created_at
                ];
            }),
            'compliance_logs' => ComplianceLog::where('user_id', $user->id)
                ->take(100)
                ->get()
                ->map(function ($log) {
                    return [
                        'event_type' => $log->event_type,
                        'event_data' => $log->event_data,
                        'created_at' => $log->created_at
                    ];
                }),
            'risk_assessment' => [
                'score' => $user->risk_score,
                'level' => $user->risk_level,
                'factors' => $user->risk_factors,
                'assessed_at' => $user->risk_assessed_at
            ],
            'export_generated_at' => now()
        ];
    }

    /**
     * Anonymize user personal data
     */
    private function anonymizeUserData(User $user): void
    {
        $user->update([
            'first_name' => 'Deleted',
            'last_name' => 'User',
            'email' => 'deleted_' . $user->uuid . '@anonymized.com',
            'phone_number' => null,
            'profile_image' => null,
            'verification_data' => null,
            'face_descriptor' => null,
            'face_verification_data' => null,
            'address_verified' => false,
            'address_verification_data' => null,
            'background_check_results' => null,
            'aml_results' => null,
            'risk_factors' => null
        ]);
    }

    /**
     * Delete associated user data
     */
    private function deleteAssociatedData(User $user): void
    {
        // Delete ID documents and files
        foreach ($user->idDocuments as $document) {
            if ($document->file_path) {
                Storage::disk('private')->delete(decrypt($document->file_path));
            }
            $document->delete();
        }

        // Delete audit logs
        $user->auditLogs()->delete();

        // Delete compliance logs
        ComplianceLog::where('user_id', $user->id)->delete();

        // Delete notification logs
        $user->notifications()->delete();

        // Anonymize votes (keep voting record but remove personal data)
        $user->voteTokens()->update([
            'user_id' => null,
            'anonymized_at' => now()
        ]);
    }

    /**
     * Get list of anonymized fields
     */
    private function getAnonymizedFields(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'profile_image',
            'verification_data',
            'face_descriptor',
            'face_verification_data',
            'address_verification_data',
            'background_check_results',
            'aml_results',
            'risk_factors'
        ];
    }

    /**
     * Get types of records deleted
     */
    private function getDeletedRecordTypes(): array
    {
        return [
            'id_documents',
            'audit_logs',
            'compliance_logs',
            'notifications',
            'vote_tokens' // Anonymized, not deleted
        ];
    }

    /**
     * Log data action for compliance
     */
    private function logDataAction(User $user, string $action, string $description, array $details = []): void
    {
        try {
            ComplianceLog::create([
                'user_id' => $user->id,
                'event_type' => 'gdpr',
                'event_subtype' => $action,
                'event_data' => $details,
                'description' => $description,
                'performed_by' => Auth::id()
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log data action', [
                'user_id' => $user->id,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process automated data cleanup
     */
    public function processAutomatedCleanup(): array
    {
        $results = [
            'processed_users' => 0,
            'deleted_users' => 0,
            'anonymized_users' => 0,
            'errors' => []
        ];

        try {
            // Find users past retention date
            $expiredUsers = User::whereNotNull('data_retention_until')
                ->where('data_retention_until', '<', now())
                ->whereNotNull('gdpr_deletion_requested_at')
                ->get();

            foreach ($expiredUsers as $user) {
                try {
                    $deleteResult = $this->deleteUserData($user);

                    if ($deleteResult['deleted']) {
                        $results['deleted_users']++;
                    } else {
                        $results['anonymized_users']++;
                    }

                    $results['processed_users']++;

                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Automated cleanup failed', ['error' => $e->getMessage()]);
            $results['errors'][] = ['general_error' => $e->getMessage()];
        }

        return $results;
    }

    /**
     * Create or update retention policy
     */
    public function createRetentionPolicy(array $data): DataRetentionPolicy
    {
        return DataRetentionPolicy::create([
            'policy_name' => $data['policy_name'],
            'policy_type' => $data['policy_type'],
            'retention_days' => $data['retention_days'],
            'description' => $data['description'] ?? null,
            'auto_delete' => $data['auto_delete'] ?? true,
            'conditions' => $data['conditions'] ?? null,
            'is_active' => $data['is_active'] ?? true
        ]);
    }

    /**
     * Get retention policy statistics
     */
    public function getRetentionStats(): array
    {
        $totalUsers = User::count();
        $withRetentionPolicy = User::whereNotNull('data_retention_until')->count();
        $expiredUsers = User::whereNotNull('data_retention_until')
            ->where('data_retention_until', '<', now())
            ->count();

        $pendingDeletion = User::whereNotNull('gdpr_deletion_requested_at')
            ->where('data_retention_until', '<', now())
            ->count();

        return [
            'total_users' => $totalUsers,
            'users_with_retention_policy' => $withRetentionPolicy,
            'policy_coverage' => $totalUsers > 0 ? round(($withRetentionPolicy / $totalUsers) * 100, 2) : 0,
            'expired_users' => $expiredUsers,
            'pending_deletion' => $pendingDeletion,
            'policies' => DataRetentionPolicy::where('is_active', true)->count()
        ];
    }
}
