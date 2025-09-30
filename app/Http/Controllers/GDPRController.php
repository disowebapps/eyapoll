<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DataRetentionService;
use App\Exceptions\AuthorizationException;
use App\Exceptions\DatabaseException;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GDPRController extends Controller
{
    private DataRetentionService $dataRetentionService;

    public function __construct(DataRetentionService $dataRetentionService)
    {
        $this->dataRetentionService = $dataRetentionService;
    }

    /**
     * Request data export
     */
    public function requestExport(Request $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->dataRetentionService->exportUserData($user);

        if (!$result['exported']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Export failed'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data export request submitted successfully',
            'download_url' => $result['download_url'],
            'expires_at' => now()->addDays(30)->toISOString()
        ]);
    }

    /**
     * Download exported data
     */
    public function downloadExport(string $uuid): BinaryFileResponse
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        // Check if user has requested export
        if (!$user->gdpr_export_requested_at) {
            abort(404, 'Export not found');
        }

        // Find the latest export file
        $files = Storage::disk('private')->files('gdpr_exports');
        $matchingFiles = array_filter($files, function ($file) use ($user) {
            return str_contains($file, "gdpr_export_{$user->uuid}_");
        });

        if (empty($matchingFiles)) {
            abort(404, 'Export file not found');
        }

        $latestFile = end($matchingFiles);
        $filePath = Storage::disk('private')->path($latestFile);

        return response()->download($filePath, 'gdpr_export.json');
    }

    /**
     * Get export status
     */
    public function exportStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'has_export' => !is_null($user->gdpr_export_requested_at),
            'export_requested_at' => $user->gdpr_export_requested_at,
            'can_download' => !is_null($user->gdpr_export_requested_at)
        ]);
    }

    /**
     * Request data deletion
     */
    public function requestDeletion(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if already requested
        if ($user->gdpr_deletion_requested_at) {
            return response()->json([
                'success' => false,
                'message' => 'Deletion already requested'
            ], 400);
        }

        // Mark for deletion (will be processed after retention period)
        $user->update([
            'gdpr_deletion_requested_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data deletion request submitted successfully',
            'estimated_completion' => $user->data_retention_until?->toISOString() ?? now()->addDays(30)->toISOString()
        ]);
    }

    /**
     * Get deletion status
     */
    public function deletionStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'deletion_requested' => !is_null($user->gdpr_deletion_requested_at),
            'deletion_requested_at' => $user->gdpr_deletion_requested_at,
            'retention_until' => $user->data_retention_until,
            'can_be_deleted' => $user->data_retention_until && $user->data_retention_until->isPast()
        ]);
    }

    /**
     * Get user data for review
     */
    public function getData(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $this->dataRetentionService->gatherUserData($user);

        return response()->json([
            'data' => $data,
            'export_generated_at' => now()
        ]);
    }

    /**
     * Get consent status
     */
    public function getConsentStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        // This would typically store consent preferences in a separate table
        // For now, return basic status
        return response()->json([
            'consents' => [
                'data_processing' => true, // Required for platform use
                'marketing' => $user->verification_data['marketing_consent'] ?? false,
                'analytics' => $user->verification_data['analytics_consent'] ?? false,
                'third_party' => $user->verification_data['third_party_consent'] ?? false
            ],
            'last_updated' => $user->updated_at
        ]);
    }

    /**
     * Update consent preferences
     */
    public function updateConsent(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'marketing' => 'required|boolean',
                'analytics' => 'required|boolean',
                'third_party' => 'required|boolean'
            ]);

            $user = $request->user();

            if (!$user) {
                throw new AuthorizationException('User not authenticated');
            }

            $verificationData = $user->verification_data ?? [];
            $verificationData['marketing_consent'] = $request->boolean('marketing');
            $verificationData['analytics_consent'] = $request->boolean('analytics');
            $verificationData['third_party_consent'] = $request->boolean('third_party');
            $verificationData['consent_updated_at'] = now();

            $user->update([
                'verification_data' => $verificationData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Consent preferences updated successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new \App\Exceptions\ValidationException(
                'Invalid consent data provided',
                ['validation_errors' => $e->errors()]
            );
        } catch (\Exception $e) {
            throw new DatabaseException(
                'Failed to update consent preferences',
                ['user_id' => $request->user()->id ?? null],
                0,
                $e
            );
        }
    }

    // Admin methods

    /**
     * Get all GDPR requests (admin)
     */
    public function getRequests(): JsonResponse
    {
        $requests = User::whereNotNull('gdpr_export_requested_at')
            ->orWhereNotNull('gdpr_deletion_requested_at')
            ->select([
                'id', 'uuid', 'first_name', 'last_name', 'email',
                'gdpr_export_requested_at', 'gdpr_deletion_requested_at',
                'data_retention_until'
            ])
            ->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    /**
     * Admin export user data
     */
    public function adminExportUserData(User $user): JsonResponse
    {
        $result = $this->dataRetentionService->exportUserData($user);

        return response()->json($result);
    }

    /**
     * Admin delete user data
     */
    public function adminDeleteUserData(User $user): JsonResponse
    {
        $result = $this->dataRetentionService->deleteUserData($user);

        return response()->json($result);
    }

    /**
     * Get GDPR statistics (admin)
     */
    public function getGDPRStats(): JsonResponse
    {
        $stats = $this->dataRetentionService->getRetentionStats();

        $gdprStats = [
            'total_users' => $stats['total_users'],
            'export_requests' => User::whereNotNull('gdpr_export_requested_at')->count(),
            'deletion_requests' => User::whereNotNull('gdpr_deletion_requested_at')->count(),
            'completed_deletions' => User::where('status', 'deleted')->count(),
            'retention_stats' => $stats
        ];

        return response()->json($gdprStats);
    }
}
