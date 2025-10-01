<?php

namespace App\Services\Utility;

use App\Models\Admin;
use App\Models\Auth\IdDocument;
use App\Models\Candidate\CandidateDocument;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReviewManagementService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Assign a reviewer to a document
     */
    public function assignReviewer(IdDocument|CandidateDocument $document, Admin $reviewer): bool
    {
        try {
            DB::transaction(function () use ($document, $reviewer) {
                $document->update([
                    'assigned_reviewer_id' => $reviewer->id,
                    'assigned_at' => now(),
                    'review_started_at' => null,
                    'review_completed_at' => null,
                    'escalated_at' => null,
                ]);

                Log::info("Document {$document->id} assigned to reviewer {$reviewer->id}");

                // Send notification to reviewer
                $this->sendAssignmentNotification($document, $reviewer);
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to assign reviewer: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get available reviewers based on workload balancing
     */
    public function getAvailableReviewers(?string $documentType = null): Collection
    {
        if (is_null($documentType)) {
            Log::info('documentType is null in getAvailableReviewers');
        }

        $reviewers = Admin::approved()
            ->where('status', 'approved')
            ->get();

        // Filter reviewers with document review permissions
        $reviewers = $reviewers->filter(function ($reviewer) {
            return $reviewer->hasPermission('review_documents') || $reviewer->is_super_admin;
        });

        // Calculate workload for each reviewer
        $reviewersWithWorkload = $reviewers->map(function ($reviewer) use ($documentType) {
            $workload = $this->calculateReviewerWorkload($reviewer, $documentType);
            $reviewer->current_workload = $workload;
            return $reviewer;
        });

        // Sort by workload (ascending) to balance load
        return $reviewersWithWorkload->sortBy('current_workload');
    }

    /**
     * Calculate current workload for a reviewer
     */
    protected function calculateReviewerWorkload(Admin $reviewer, ?string $documentType = null): int
    {
        if (is_null($documentType)) {
            Log::info('documentType is null in calculateReviewerWorkload');
        }

        $query = IdDocument::where('assigned_reviewer_id', $reviewer->id)
            ->where('status', 'pending');

        if ($documentType) {
            $query->where('document_type', $documentType);
        }

        $idDocuments = $query->count();

        $candidateQuery = CandidateDocument::where('assigned_reviewer_id', $reviewer->id)
            ->where('status', 'pending');

        if ($documentType) {
            $candidateQuery->where('document_type', $documentType);
        }

        $candidateDocuments = $candidateQuery->count();

        return $idDocuments + $candidateDocuments;
    }

    /**
     * Auto-assign reviewers to pending documents
     */
    public function autoAssignReviewers(): array
    {
        $stats = [
            'id_documents_assigned' => 0,
            'candidate_documents_assigned' => 0,
            'errors' => 0,
        ];

        // Get unassigned pending documents
        $unassignedIdDocuments = IdDocument::where('status', 'pending')
            ->whereNull('assigned_reviewer_id')
            ->get();

        $unassignedCandidateDocuments = CandidateDocument::where('status', 'pending')
            ->whereNull('assigned_reviewer_id')
            ->get();

        $availableReviewers = $this->getAvailableReviewers();

        if ($availableReviewers->isEmpty()) {
            Log::warning('No available reviewers for auto-assignment');
            return $stats;
        }

        // Assign ID documents
        foreach ($unassignedIdDocuments as $document) {
            $reviewer = $this->selectReviewerForDocument($availableReviewers, $document);
            if ($reviewer && $this->assignReviewer($document, $reviewer)) {
                $stats['id_documents_assigned']++;
            } else {
                $stats['errors']++;
            }
        }

        // Assign candidate documents
        foreach ($unassignedCandidateDocuments as $document) {
            $reviewer = $this->selectReviewerForDocument($availableReviewers, $document);
            if ($reviewer && $this->assignReviewer($document, $reviewer)) {
                $stats['candidate_documents_assigned']++;
            } else {
                $stats['errors']++;
            }
        }

        return $stats;
    }

    /**
     * Select appropriate reviewer for a document
     */
    protected function selectReviewerForDocument(Collection $reviewers, IdDocument|CandidateDocument $document): ?Admin
    {
        // Simple round-robin for now, can be enhanced with more sophisticated logic
        static $lastAssignedIndex = 0;

        if ($reviewers->isEmpty()) {
            return null;
        }

        $reviewer = $reviewers->values()[$lastAssignedIndex % $reviewers->count()];
        $lastAssignedIndex++;

        return $reviewer;
    }

    /**
     * Start review process for a document
     */
    public function startReview(IdDocument|CandidateDocument $document, Admin $reviewer): bool
    {
        if ($document->assigned_reviewer_id !== $reviewer->id) {
            return false;
        }

        $document->update([
            'review_started_at' => now(),
        ]);

        Log::info("Review started for document {$document->id} by reviewer {$reviewer->id}");

        return true;
    }

    /**
     * Complete review process for a document
     */
    public function completeReview(IdDocument|CandidateDocument $document, Admin $reviewer, string $status, ?string $reason = null): bool
    {
        if (is_null($reason)) {
            Log::info('reason is null in completeReview');
        }

        if ($document->assigned_reviewer_id !== $reviewer->id) {
            return false;
        }

        $updateData = [
            'status' => $status,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_completed_at' => now(),
        ];

        if ($reason) {
            $updateData['rejection_reason'] = $reason;
        }

        $document->update($updateData);

        Log::info("Review completed for document {$document->id} by reviewer {$reviewer->id} with status {$status}");

        return true;
    }

    /**
     * Escalate overdue reviews
     */
    public function escalateOverdueReviews(int $hoursThreshold = 24): array
    {
        $threshold = now()->subHours($hoursThreshold);

        $stats = [
            'id_documents_escalated' => 0,
            'candidate_documents_escalated' => 0,
            'notifications_sent' => 0,
        ];

        // Escalate ID documents
        $overdueIdDocuments = IdDocument::where('status', 'pending')
            ->where('assigned_at', '<', $threshold)
            ->whereNull('escalated_at')
            ->get();

        foreach ($overdueIdDocuments as $document) {
            $document->update(['escalated_at' => now()]);
            $this->sendEscalationNotification($document);
            $stats['id_documents_escalated']++;
            $stats['notifications_sent']++;
        }

        // Escalate candidate documents
        $overdueCandidateDocuments = CandidateDocument::where('status', 'pending')
            ->where('assigned_at', '<', $threshold)
            ->whereNull('escalated_at')
            ->get();

        foreach ($overdueCandidateDocuments as $document) {
            $document->update(['escalated_at' => now()]);
            $this->sendEscalationNotification($document);
            $stats['candidate_documents_escalated']++;
            $stats['notifications_sent']++;
        }

        return $stats;
    }

    /**
     * Calculate QA metrics for a reviewer
     */
    public function calculateQAMetrics(Admin $reviewer, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        if (is_null($startDate)) {
            Log::info('startDate is null in calculateQAMetrics');
        }
        if (is_null($endDate)) {
            Log::info('endDate is null in calculateQAMetrics');
        }

        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        // Get reviews completed by this reviewer in the period
        $idDocumentsReviewed = IdDocument::where('reviewed_by', $reviewer->id)
            ->whereBetween('reviewed_at', [$startDate, $endDate])
            ->get();

        $candidateDocumentsReviewed = CandidateDocument::where('reviewed_by', $reviewer->id)
            ->whereBetween('reviewed_at', [$startDate, $endDate])
            ->get();

        $allReviews = $idDocumentsReviewed->merge($candidateDocumentsReviewed);

        if ($allReviews->isEmpty()) {
            return [
                'total_reviews' => 0,
                'accuracy_rate' => 0,
                'average_speed_hours' => 0,
                'consistency_score' => 0,
            ];
        }

        // Calculate accuracy (assuming approved reviews are correct, can be enhanced with audit data)
        $approvedCount = $allReviews->where('status', 'approved')->count();
        $accuracyRate = ($approvedCount / $allReviews->count()) * 100;

        // Calculate average speed
        $totalTime = 0;
        $reviewsWithTime = 0;

        foreach ($allReviews as $review) {
            if ($review->review_started_at && $review->review_completed_at) {
                $timeSpent = $review->review_started_at->diffInHours($review->review_completed_at);
                $totalTime += $timeSpent;
                $reviewsWithTime++;
            }
        }

        $averageSpeed = $reviewsWithTime > 0 ? $totalTime / $reviewsWithTime : 0;

        // Consistency score (placeholder - can be enhanced with more metrics)
        $consistencyScore = min(100, max(0, 100 - ($averageSpeed * 5))); // Simple inverse relationship

        return [
            'total_reviews' => $allReviews->count(),
            'accuracy_rate' => round($accuracyRate, 2),
            'average_speed_hours' => round($averageSpeed, 2),
            'consistency_score' => round($consistencyScore, 2),
        ];
    }

    /**
     * Get review analytics
     */
    public function getReviewAnalytics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        if (is_null($startDate)) {
            Log::info('startDate is null in getReviewAnalytics');
        }
        if (is_null($endDate)) {
            Log::info('endDate is null in getReviewAnalytics');
        }

        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $idDocuments = IdDocument::whereBetween('created_at', [$startDate, $endDate])->get();
        $candidateDocuments = CandidateDocument::whereBetween('created_at', [$startDate, $endDate])->get();

        $totalDocuments = $idDocuments->count() + $candidateDocuments->count();
        $reviewedDocuments = $idDocuments->whereNotNull('reviewed_at')->count() +
                           $candidateDocuments->whereNotNull('reviewed_at')->count();

        $pendingDocuments = $idDocuments->where('status', 'pending')->count() +
                          $candidateDocuments->where('status', 'pending')->count();

        $approvedDocuments = $idDocuments->where('status', 'approved')->count() +
                           $candidateDocuments->where('status', 'approved')->count();

        $rejectedDocuments = $idDocuments->where('status', 'rejected')->count() +
                           $candidateDocuments->where('status', 'rejected')->count();

        // Calculate average review time
        $totalReviewTime = 0;
        $reviewsWithTime = 0;

        foreach ([$idDocuments, $candidateDocuments] as $documents) {
            foreach ($documents as $doc) {
                if ($doc->review_started_at && $doc->review_completed_at) {
                    $totalReviewTime += $doc->review_started_at->diffInMinutes($doc->review_completed_at);
                    $reviewsWithTime++;
                }
            }
        }

        $averageReviewTime = $reviewsWithTime > 0 ? $totalReviewTime / $reviewsWithTime : 0;

        return [
            'total_documents' => $totalDocuments,
            'reviewed_documents' => $reviewedDocuments,
            'pending_documents' => $pendingDocuments,
            'approved_documents' => $approvedDocuments,
            'rejected_documents' => $rejectedDocuments,
            'review_completion_rate' => $totalDocuments > 0 ? round(($reviewedDocuments / $totalDocuments) * 100, 2) : 0,
            'average_review_time_minutes' => round($averageReviewTime, 2),
        ];
    }

    /**
     * Send assignment notification
     */
    protected function sendAssignmentNotification(IdDocument|CandidateDocument $document, Admin $reviewer): void
    {
        try {
            $this->notificationService->send(
                $reviewer,
                'document_assigned_for_review',
                [
                    'document_type' => $document->document_type,
                    'document_id' => $document->id,
                    'assigned_at' => now()->format('Y-m-d H:i:s'),
                ],
                'in_app'
            );
        } catch (\Exception $e) {
            Log::error("Failed to send assignment notification: {$e->getMessage()}");
        }
    }

    /**
     * Send escalation notification
     */
    protected function sendEscalationNotification(IdDocument|CandidateDocument $document): void
    {
        try {
            // Send to all admins with review permissions
            $admins = Admin::approved()
                ->where('status', 'approved')
                ->filter(function ($admin) {
                    return $admin->hasPermission('review_documents') || $admin->is_super_admin;
                })
                ->get();

            foreach ($admins as $admin) {
                $this->notificationService->send(
                    $admin,
                    'document_review_escalated',
                    [
                        'document_type' => $document->document_type,
                        'document_id' => $document->id,
                        'assigned_reviewer_id' => $document->assigned_reviewer_id,
                        'escalated_at' => now()->format('Y-m-d H:i:s'),
                    ],
                    'in_app'
                );
            }
        } catch (\Exception $e) {
            Log::error("Failed to send escalation notification: {$e->getMessage()}");
        }
    }
}
