<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Models\Admin;
use App\Enums\Election\ElectionStatus;
use App\Services\Cryptographic\CryptographicService;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ElectionCertificationService
{
    public function __construct(
        private CryptographicService $crypto,
        private AuditLogService $auditLog
    ) {}

    /**
     * Certify election results
     */
    public function certifyElection(Election $election, Admin $certifier, array $certificationData = []): bool
    {
        if (!$election->status->canBeCertified()) {
            throw new \InvalidArgumentException("Election in status '{$election->status->value}' cannot be certified");
        }

        return DB::transaction(function () use ($election, $certifier, $certificationData) {
            // Generate certification hash
            $certificationHash = $this->generateCertificationHash($election, $certifier, $certificationData);

            // Update election status
            $election->update([
                'status' => ElectionStatus::CERTIFIED,
                'certified_at' => now(),
                'certified_by' => $certifier->id,  // Using foreign key column name
                'certification_hash' => $certificationHash,
                'certification_data' => $certificationData,
            ]);
            
            // Refresh the relationship
            $election->load('certifier');

            // Log the certification
            $this->auditLog->log(
                'election_certified',
                $certifier,
                'elections',
                $election->id,
                [
                    'certification_hash' => $certificationHash,
                    'certification_data' => $certificationData,
                ]
            );

            Log::info('Election certified', [
                'election_id' => $election->id,
                'certifier_id' => $certifier->id,
                'certification_hash' => $certificationHash,
            ]);

            return true;
        });
    }

    /**
     * Verify election certification
     */
    public function verifyCertification(Election $election): array
    {
        if ($election->status !== ElectionStatus::CERTIFIED) {
            return [
                'valid' => false,
                'reason' => 'Election is not certified',
            ];
        }

        $expectedHash = $this->generateCertificationHash(
            $election,
            $election->certifier,
            $election->certification_data ?? []
        );

        $isValid = hash_equals($expectedHash, $election->certification_hash);

        return [
            'valid' => $isValid,
            'expected_hash' => $expectedHash,
            'actual_hash' => $election->certification_hash,
            'reason' => $isValid ? null : 'Certification hash mismatch',
        ];
    }

    /**
     * Generate certification hash
     */
    private function generateCertificationHash(Election $election, Admin $certifier, array $certificationData): string
    {
        $certificationPayload = [
            'election_id' => $election->id,
            'election_uuid' => $election->uuid,
            'election_title' => $election->title,
            'election_status' => $election->status->value,
            'certifier_id' => $certifier->id,
            'certifier_name' => $certifier->full_name,
            'certified_at' => now()->toISOString(),
            'results_summary' => $this->getElectionResultsSummary($election),
            'certification_data' => $certificationData,
        ];

        return $this->crypto->generateAuditHash($certificationPayload);
    }

    /**
     * Get election results summary for certification
     */
    private function getElectionResultsSummary(Election $election): array
    {
        $summary = [
            'total_votes' => 0,
            'total_positions' => $election->positions()->count(),
            'positions' => [],
        ];

        foreach ($election->positions as $position) {
            $tallies = $position->tallies()->with('candidate')->get();
            $positionSummary = [
                'position_id' => $position->id,
                'position_title' => $position->title,
                'total_votes' => $tallies->sum('vote_count'),
                'candidates' => [],
            ];

            foreach ($tallies as $tally) {
                $positionSummary['candidates'][] = [
                    'candidate_id' => $tally->candidate_id,
                    'candidate_name' => $tally->candidate->user->full_name,
                    'vote_count' => $tally->vote_count,
                    'percentage' => $positionSummary['total_votes'] > 0
                        ? round(($tally->vote_count / $positionSummary['total_votes']) * 100, 2)
                        : 0,
                ];
            }

            $summary['positions'][] = $positionSummary;
            $summary['total_votes'] += $positionSummary['total_votes'];
        }

        return $summary;
    }

    /**
     * Check if election can be certified
     */
    public function canCertifyElection(Election $election): array
    {
        $issues = [];

        // Check status
        if (!$election->status->canBeCertified()) {
            $issues[] = "Election status '{$election->status->value}' does not allow certification";
        }

        // Check for active appeals
        $activeAppeals = $election->appeals()
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        if ($activeAppeals > 0) {
            $issues[] = "Election has {$activeAppeals} active appeal(s) that must be resolved";
        }

        // Check results integrity
        $integrityCheck = $this->checkResultsIntegrity($election);
        if (!$integrityCheck['valid']) {
            $issues[] = 'Results integrity check failed: ' . $integrityCheck['reason'];
        }

        // Check minimum time since completion
        $hoursSinceCompletion = $election->ends_at ? now()->diffInHours($election->ends_at) : 0;
        $minHours = config('elections.certification.min_hours_after_completion', 24);

        if ($hoursSinceCompletion < $minHours) {
            $issues[] = "Election must be completed for at least {$minHours} hours before certification";
        }

        return [
            'can_certify' => empty($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Check results integrity
     */
    private function checkResultsIntegrity(Election $election): array
    {
        try {
            // Check if all positions have results
            $positionsWithoutResults = $election->positions()
                ->whereDoesntHave('tallies')
                ->count();

            if ($positionsWithoutResults > 0) {
                return [
                    'valid' => false,
                    'reason' => "{$positionsWithoutResults} position(s) have no results",
                ];
            }

            // Check vote totals consistency
            foreach ($election->positions as $position) {
                $tallyTotal = $position->tallies()->sum('vote_count');
                $voteTotal = $position->votes()->count();

                if ($tallyTotal !== $voteTotal) {
                    return [
                        'valid' => false,
                        'reason' => "Vote tally mismatch for position '{$position->title}': tally={$tallyTotal}, votes={$voteTotal}",
                    ];
                }
            }

            return ['valid' => true];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'reason' => 'Integrity check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get certification statistics
     */
    public function getCertificationStatistics(): array
    {
        $totalElections = Election::count();
        $certifiedElections = Election::where('status', ElectionStatus::CERTIFIED)->count();
        $finalizedElections = Election::where('status', ElectionStatus::FINALIZED)->count();

        return [
            'total_elections' => $totalElections,
            'certified_elections' => $certifiedElections,
            'finalized_elections' => $finalizedElections,
            'certification_rate' => $totalElections > 0 ? round(($certifiedElections / $totalElections) * 100, 2) : 0,
            'finalization_rate' => $totalElections > 0 ? round(($finalizedElections / $totalElections) * 100, 2) : 0,
        ];
    }
}
