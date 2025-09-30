<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Enums\Election\ElectionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ElectionArchiveService
{
    public function archiveElection(Election $election, bool $force = false): bool
    {
        if (!$force && !$this->canArchiveElection($election)) {
            throw new \InvalidArgumentException('Election does not meet archiving criteria');
        }

        try {
            return DB::transaction(function () use ($election) {
                // Create comprehensive archive snapshot
                $this->createArchiveSnapshot($election);

                // Update status to archived
                $election->update(['status' => ElectionStatus::ARCHIVED]);

                // Send notifications to stakeholders
                $this->notifyArchiving($election);

                // Audit logging
                $this->logArchivingAction($election);

                Log::info('Election archived successfully', [
                    'election_id' => $election->id,
                    'election_title' => $election->title,
                    'archived_at' => now(),
                ]);

                return true;
            });
        } catch (Exception $e) {
            Log::error('Failed to archive election', [
                'election_id' => $election->id,
                'election_title' => $election->title,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function unarchiveElection(Election $election): bool
    {
        if ($election->status !== ElectionStatus::ARCHIVED) {
            throw new \InvalidArgumentException('Election is not archived');
        }

        try {
            return DB::transaction(function () use ($election) {
                // Restore to completed status
                $election->update(['status' => ElectionStatus::COMPLETED]);

                Log::info('Election unarchived', [
                    'election_id' => $election->id,
                    'election_title' => $election->title,
                    'unarchived_at' => now(),
                ]);

                return true;
            });
        } catch (Exception $e) {
            Log::error('Failed to unarchive election', [
                'election_id' => $election->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function canArchiveElection(Election $election): bool
    {
        // Check basic eligibility criteria
        if (!$election->status->canBeArchived()) {
            return false;
        }

        if (!$election->results_published) {
            return false;
        }

        if (!$election->ends_at || $election->ends_at >= now()->subDays(30)) {
            return false;
        }

        // Check for active appeals
        if ($this->hasActiveAppeals($election)) {
            return false;
        }

        // Check for pending certifications (if applicable)
        // Add any additional certification checks here

        return true;
    }

    public function getArchivableElections()
    {
        return Election::whereIn('status', ElectionStatus::getArchivableStatuses())
            ->where('results_published', true)
            ->where('ends_at', '<', now()->subDays(30))
            ->whereDoesntHave('appeals', function ($query) {
                $query->whereIn('status', [\App\Enums\Appeal\AppealStatus::SUBMITTED, \App\Enums\Appeal\AppealStatus::UNDER_REVIEW]);
            })
            ->get();
    }

    private function hasActiveAppeals(Election $election): bool
    {
        return $election->appeals()
            ->whereIn('status', [\App\Enums\Appeal\AppealStatus::SUBMITTED, \App\Enums\Appeal\AppealStatus::UNDER_REVIEW])
            ->exists();
    }

    private function createArchiveSnapshot(Election $election): void
    {
        try {
            $election->snapshots()->create([
                'type' => 'archive',
                'data' => [
                    // Election metadata
                    'election_metadata' => [
                        'title' => $election->title,
                        'type' => $election->type->value,
                        'starts_at' => $election->starts_at,
                        'ends_at' => $election->ends_at,
                        'created_by' => $election->created_by,
                    ],
                    // Final results and statistics
                    'final_results' => [
                        'total_votes' => $election->getTotalVoteRecords(),
                        'voter_turnout' => $election->getVoterTurnout(),
                        'positions_count' => $election->positions()->count(),
                        'candidates_count' => $election->candidates()->count(),
                    ],
                    // Voter turnout data
                    'voter_data' => [
                        'total_eligible_voters' => $election->voteTokens()->count(),
                        'total_votes_cast' => $election->voteRecords()->count(),
                        'turnout_percentage' => $election->getVoterTurnout()['percentage'],
                    ],
                    // Candidate information
                    'candidate_data' => [
                        'total_candidates' => $election->candidates()->count(),
                        'approved_candidates' => $election->approvedCandidates()->count(),
                    ],
                    // Audit trail summary
                    'audit_summary' => [
                        'total_appeals' => $election->appeals()->count(),
                        'resolved_appeals' => $election->appeals()->resolved()->count(),
                        'pending_appeals' => $election->appeals()->whereIn('status', [\App\Enums\Appeal\AppealStatus::SUBMITTED, \App\Enums\Appeal\AppealStatus::UNDER_REVIEW])->count(),
                    ],
                    // Archiving metadata
                    'archiving_info' => [
                        'archived_at' => now(),
                        'archived_by' => auth()->id() ?? 'system',
                        'archiving_reason' => 'Automatic archiving after 30 days',
                    ],
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create archive snapshot', [
                'election_id' => $election->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function notifyArchiving(Election $election): void
    {
        try {
            // For now, just log the notification. In a production system,
            // this would integrate with the notification system to alert stakeholders
            Log::info('Election archiving notification', [
                'election_id' => $election->id,
                'election_title' => $election->title,
                'notification_type' => 'election_archived',
                'message' => "Election '{$election->title}' has been automatically archived after 30 days",
                'archived_at' => now(),
                'stakeholders_notified' => 'admins', // Would be expanded to actual notification sending
            ]);
        } catch (Exception $e) {
            Log::warning('Failed to log archiving notification', [
                'election_id' => $election->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw exception - archiving should continue even if notifications fail
        }
    }

    private function logArchivingAction(Election $election): void
    {
        try {
            // Log to Laravel's standard log - audit logging can be enhanced later
            Log::info('Election archiving audit', [
                'action' => 'election_archived',
                'election_id' => $election->id,
                'election_title' => $election->title,
                'archived_at' => now(),
                'archived_by' => 'system', // Since this is automated
                'reason' => 'Automatic archiving after 30 days',
                'final_turnout' => $election->getVoterTurnout(),
                'total_votes' => $election->getTotalVoteRecords(),
            ]);
        } catch (Exception $e) {
            Log::warning('Failed to log archiving audit action', [
                'election_id' => $election->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw exception - archiving should continue even if audit logging fails
        }
    }
}