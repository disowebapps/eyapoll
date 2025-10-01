<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Models\Admin;
use App\Enums\Election\ElectionStatus;
use App\Services\Cryptographic\CryptographicService;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ElectionFinalizationService
{
    public function __construct(
        private CryptographicService $crypto,
        private AuditLogService $auditLog
    ) {}

    /**
     * Finalize election results
     */
    public function finalizeElection(Election $election, Admin $finalizer, array $finalizationData = []): bool
    {
        if (!$election->status->canBeFinalized()) {
            throw new \InvalidArgumentException("Election in status '{$election->status->value}' cannot be finalized");
        }

        return DB::transaction(function () use ($election, $finalizer, $finalizationData) {
            // Generate finalization hash
            $finalizationHash = $this->generateFinalizationHash($election, $finalizer, $finalizationData);

            // Update election status
            $election->update([
                'status' => ElectionStatus::FINALIZED,
                'finalized_at' => now(),
                'finalized_by' => $finalizer->id,
                'finalization_hash' => $finalizationHash,
                'finalization_data' => $finalizationData,
            ]);

            // Log the finalization
            $this->auditLog->log(
                'election_finalized',
                $finalizer,
                'elections',
                $election->id,
                [
                    'finalization_hash' => $finalizationHash,
                    'finalization_data' => $finalizationData,
                ]
            );

            Log::info('Election finalized', [
                'election_id' => $election->id,
                'finalizer_id' => $finalizer->id,
                'finalization_hash' => $finalizationHash,
            ]);

            return true;
        });
    }

    /**
     * Verify election finalization
     */
    public function verifyFinalization(Election $election): array
    {
        if ($election->status !== ElectionStatus::FINALIZED) {
            return [
                'valid' => false,
                'reason' => 'Election is not finalized',
            ];
        }

        $expectedHash = $this->generateFinalizationHash(
            $election,
            $election->finalizer,
            $election->finalization_data ?? []
        );

        $isValid = hash_equals($expectedHash, $election->finalization_hash);

        return [
            'valid' => $isValid,
            'expected_hash' => $expectedHash,
            'actual_hash' => $election->finalization_hash,
            'reason' => $isValid ? null : 'Finalization hash mismatch',
        ];
    }

    /**
     * Generate finalization hash
     */
    private function generateFinalizationHash(Election $election, Admin $finalizer, array $finalizationData): string
    {
        $finalizationPayload = [
            'election_id' => $election->id,
            'election_uuid' => $election->uuid,
            'election_title' => $election->title,
            'certification_hash' => $election->certification_hash,
            'finalizer_id' => $finalizer->id,
            'finalizer_name' => $finalizer->full_name,
            'finalized_at' => now()->toISOString(),
            'final_results_summary' => $this->getFinalResultsSummary($election),
            'finalization_data' => $finalizationData,
        ];

        return $this->crypto->generateAuditHash($finalizationPayload);
    }

    /**
     * Get final results summary for finalization
     */
    private function getFinalResultsSummary(Election $election): array
    {
        $summary = [
            'election_id' => $election->id,
            'election_title' => $election->title,
            'certified_at' => $election->certified_at?->toISOString(),
            'total_positions' => $election->positions()->count(),
            'total_votes_cast' => 0,
            'positions' => [],
            'winners' => [],
        ];

        foreach ($election->positions as $position) {
            $tallies = $position->tallies()->with('candidate')->orderBy('vote_count', 'desc')->get();
            $totalVotes = $tallies->sum('vote_count');

            $positionSummary = [
                'position_id' => $position->id,
                'position_title' => $position->title,
                'total_votes' => $totalVotes,
                'candidates_count' => $tallies->count(),
                'winner' => null,
                'candidates' => [],
            ];

            foreach ($tallies as $tally) {
                $candidateData = [
                    'candidate_id' => $tally->candidate_id,
                    'candidate_name' => $tally->candidate->user->full_name,
                    'vote_count' => $tally->vote_count,
                    'percentage' => $totalVotes > 0 ? round(($tally->vote_count / $totalVotes) * 100, 2) : 0,
                ];

                $positionSummary['candidates'][] = $candidateData;

                // First candidate is the winner
                if (!$positionSummary['winner']) {
                    $positionSummary['winner'] = $candidateData;
                    $summary['winners'][] = [
                        'position_id' => $position->id,
                        'position_title' => $position->title,
                        'winner' => $candidateData,
                    ];
                }
            }

            $summary['positions'][] = $positionSummary;
            $summary['total_votes_cast'] += $totalVotes;
        }

        return $summary;
    }

    /**
     * Check if election can be finalized
     */
    public function canFinalizeElection(Election $election): array
    {
        $issues = [];

        // Check status
        if (!$election->status->canBeFinalized()) {
            $issues[] = "Election status '{$election->status->value}' does not allow finalization";
        }

        // Check certification
        if (!$election->certified_at) {
            $issues[] = "Election must be certified before finalization";
        }

        // Check certification validity
        $certificationService = app(ElectionCertificationService::class);
        $certificationCheck = $certificationService->verifyCertification($election);
        if (!$certificationCheck['valid']) {
            $issues[] = 'Certification verification failed: ' . $certificationCheck['reason'];
        }

        // Check appeals deadline
        $appealsDeadline = $this->calculateAppealsDeadline($election);
        if (now()->isBefore($appealsDeadline)) {
            $issues[] = "Appeals deadline has not passed yet. Finalization allowed after: {$appealsDeadline->format('Y-m-d H:i:s')}";
        }

        // Check for unresolved appeals
        $unresolvedAppeals = $election->appeals()
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        if ($unresolvedAppeals > 0) {
            $issues[] = "Election has {$unresolvedAppeals} unresolved appeal(s)";
        }

        // Check minimum time since certification
        $hoursSinceCertification = $election->certified_at ? now()->diffInHours($election->certified_at) : 0;
        $minHours = config('elections.finalization.min_hours_after_certification', 48);

        if ($hoursSinceCertification < $minHours) {
            $issues[] = "Election must be certified for at least {$minHours} hours before finalization";
        }

        return [
            'can_finalize' => empty($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Calculate appeals deadline
     */
    private function calculateAppealsDeadline(Election $election): \Carbon\Carbon
    {
        // Appeals deadline is typically 30 days after election ends
        $appealsPeriodDays = config('elections.appeals.deadline_days', 30);
        return $election->ends_at->copy()->addDays($appealsPeriodDays);
    }

    /**
     * Generate final audit report
     */
    public function generateFinalAuditReport(Election $election): array
    {
        if ($election->status !== ElectionStatus::FINALIZED) {
            throw new \InvalidArgumentException('Can only generate final audit report for finalized elections');
        }

        return [
            'election_info' => [
                'id' => $election->id,
                'uuid' => $election->uuid,
                'title' => $election->title,
                'type' => $election->type->label(),
                'status' => $election->status->label(),
                'start_date' => $election->starts_at?->format('Y-m-d H:i:s'),
                'end_date' => $election->ends_at?->format('Y-m-d H:i:s'),
                'certified_at' => $election->certified_at?->format('Y-m-d H:i:s'),
                'finalized_at' => $election->finalized_at?->format('Y-m-d H:i:s'),
            ],
            'certification_info' => [
                'certifier' => $election->certifier?->full_name,
                'certification_hash' => $election->certification_hash,
                'certification_data' => $election->certification_data,
            ],
            'finalization_info' => [
                'finalizer' => $election->finalizer?->full_name,
                'finalization_hash' => $election->finalization_hash,
                'finalization_data' => $election->finalization_data,
            ],
            'results_summary' => $this->getFinalResultsSummary($election),
            'appeals_summary' => $this->getAppealsSummary($election),
            'integrity_checks' => [
                'certification_valid' => app(ElectionCertificationService::class)->verifyCertification($election)['valid'],
                'finalization_valid' => $this->verifyFinalization($election)['valid'],
            ],
            'audit_trail' => $this->getAuditTrail($election),
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Get appeals summary
     */
    private function getAppealsSummary(Election $election): array
    {
        $totalAppeals = $election->appeals()->count();
        $resolvedAppeals = $election->appeals()->whereIn('status', ['approved', 'rejected', 'dismissed'])->count();
        $pendingAppeals = $election->appeals()->whereIn('status', ['submitted', 'under_review'])->count();

        return [
            'total_appeals' => $totalAppeals,
            'resolved_appeals' => $resolvedAppeals,
            'pending_appeals' => $pendingAppeals,
            'resolution_rate' => $totalAppeals > 0 ? round(($resolvedAppeals / $totalAppeals) * 100, 2) : 0,
        ];
    }

    /**
     * Get audit trail
     */
    private function getAuditTrail(Election $election): array
    {
        // This would typically query the audit logs for this election
        // For now, return a placeholder
        return [
            'total_audit_entries' => 0,
            'key_events' => [],
        ];
    }

    /**
     * Get finalization statistics
     */
    public function getFinalizationStatistics(): array
    {
        $totalElections = Election::count();
        $finalizedElections = Election::where('status', ElectionStatus::FINALIZED)->count();
        $archivedElections = Election::where('status', ElectionStatus::ARCHIVED)->count();

        return [
            'total_elections' => $totalElections,
            'finalized_elections' => $finalizedElections,
            'archived_elections' => $archivedElections,
            'finalization_rate' => $totalElections > 0 ? round(($finalizedElections / $totalElections) * 100, 2) : 0,
            'archive_rate' => $totalElections > 0 ? round(($archivedElections / $totalElections) * 100, 2) : 0,
        ];
    }
}
