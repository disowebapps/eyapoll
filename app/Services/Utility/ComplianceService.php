<?php

namespace App\Services;

use App\Models\User;
use App\Models\ComplianceLog;
use App\Models\RegulatoryReport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComplianceService
{
    /**
     * Perform AML screening on a user
     */
    public function performAMLScreening(User $user): array
    {
        try {
            $screeningResult = [
                'screened' => false,
                'risk_level' => 'unknown',
                'flags' => [],
                'recommendations' => [],
                'screening_data' => null
            ];

            // Check if already screened recently (within 30 days)
            if ($user->aml_screened_at && $user->aml_screened_at->diffInDays(now()) < 30) {
                return [
                    'screened' => true,
                    'previously_screened' => true,
                    'last_screening' => $user->aml_screened_at,
                    'results' => $user->aml_results
                ];
            }

            // Perform basic AML checks
            $flags = $this->performBasicAMLChecks($user);

            // Determine risk level
            $riskLevel = $this->calculateRiskLevel($flags);

            // Generate recommendations
            $recommendations = $this->generateAMLRecommendations($flags, $riskLevel);

            $screeningResult = [
                'screened' => true,
                'risk_level' => $riskLevel,
                'flags' => $flags,
                'recommendations' => $recommendations,
                'screening_data' => [
                    'screened_at' => now(),
                    'screening_method' => 'basic_automated',
                    'flags_count' => count($flags)
                ]
            ];

            // Update user record
            $user->update([
                'aml_screened' => true,
                'aml_screened_at' => now(),
                'aml_results' => json_encode($screeningResult),
                'compliance_status' => $this->determineComplianceStatus($riskLevel, $flags)
            ]);

            // Log the screening
            $this->logComplianceEvent($user, 'aml_screening', 'completed', [
                'risk_level' => $riskLevel,
                'flags_count' => count($flags),
                'recommendations_count' => count($recommendations)
            ]);

            return $screeningResult;

        } catch (\Exception $e) {
            Log::error('AML screening failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            $this->logComplianceEvent($user, 'aml_screening', 'failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'screened' => false,
                'error' => 'AML screening failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Perform basic AML checks
     */
    private function performBasicAMLChecks(User $user): array
    {
        $flags = [];

        // Check for suspicious patterns in user data
        if ($this->hasSuspiciousName($user)) {
            $flags[] = [
                'type' => 'suspicious_name',
                'severity' => 'high',
                'description' => 'Name matches known high-risk patterns'
            ];
        }

        // Check for incomplete verification
        if (!$user->hasVerifiedDocuments()) {
            $flags[] = [
                'type' => 'incomplete_verification',
                'severity' => 'medium',
                'description' => 'User lacks verified identity documents'
            ];
        }

        // Check for rapid account creation and activity
        if ($this->hasRapidActivity($user)) {
            $flags[] = [
                'type' => 'rapid_activity',
                'severity' => 'medium',
                'description' => 'Unusual account activity patterns detected'
            ];
        }

        // Check for international indicators
        if ($this->hasInternationalFlags($user)) {
            $flags[] = [
                'type' => 'international_activity',
                'severity' => 'low',
                'description' => 'International account indicators'
            ];
        }

        // Check for document inconsistencies
        if ($this->hasDocumentInconsistencies($user)) {
            $flags[] = [
                'type' => 'document_inconsistencies',
                'severity' => 'high',
                'description' => 'Inconsistencies found in submitted documents'
            ];
        }

        return $flags;
    }

    /**
     * Calculate overall risk level
     */
    private function calculateRiskLevel(array $flags): string
    {
        $highRiskCount = 0;
        $mediumRiskCount = 0;

        foreach ($flags as $flag) {
            if ($flag['severity'] === 'high') {
                $highRiskCount++;
            } elseif ($flag['severity'] === 'medium') {
                $mediumRiskCount++;
            }
        }

        if ($highRiskCount > 0) {
            return 'high';
        } elseif ($mediumRiskCount > 2) {
            return 'medium';
        } elseif ($mediumRiskCount > 0 || count($flags) > 0) {
            return 'low';
        }

        return 'clear';
    }

    /**
     * Generate AML recommendations
     */
    private function generateAMLRecommendations(array $flags, string $riskLevel): array
    {
        $recommendations = [];

        if ($riskLevel === 'high') {
            $recommendations[] = 'Immediate manual review required';
            $recommendations[] = 'Enhanced due diligence needed';
            $recommendations[] = 'Transaction monitoring recommended';
        } elseif ($riskLevel === 'medium') {
            $recommendations[] = 'Additional verification documents requested';
            $recommendations[] = 'Manual review recommended';
        }

        // Specific recommendations based on flags
        foreach ($flags as $flag) {
            switch ($flag['type']) {
                case 'incomplete_verification':
                    $recommendations[] = 'Request additional identity verification';
                    break;
                case 'document_inconsistencies':
                    $recommendations[] = 'Review submitted documents manually';
                    break;
                case 'rapid_activity':
                    $recommendations[] = 'Monitor account activity closely';
                    break;
            }
        }

        return array_unique($recommendations);
    }

    /**
     * Determine compliance status
     */
    private function determineComplianceStatus(string $riskLevel, array $flags): string
    {
        if ($riskLevel === 'high') {
            return 'flagged';
        } elseif ($riskLevel === 'medium') {
            return 'review_required';
        } elseif (count($flags) === 0) {
            return 'cleared';
        }

        return 'cleared';
    }

    /**
     * Check for suspicious name patterns
     */
    private function hasSuspiciousName(User $user): bool
    {
        $fullName = strtolower($user->full_name);

        // Simple check for common test/fake names
        $suspiciousPatterns = [
            'test user',
            'fake name',
            'john doe',
            'jane doe',
            'user user'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($fullName, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for rapid account activity
     */
    private function hasRapidActivity(User $user): bool
    {
        // Check if account was created and immediately started activities
        $documentsCount = $user->idDocuments()->count();
        $hoursSinceCreation = $user->created_at->diffInHours(now());

        // Flag if many documents uploaded in short time
        return $documentsCount > 3 && $hoursSinceCreation < 24;
    }

    /**
     * Check for international flags
     */
    private function hasInternationalFlags(User $user): bool
    {
        // Simple check - in production, use proper geolocation services
        return false; // Placeholder
    }

    /**
     * Check for document inconsistencies
     */
    private function hasDocumentInconsistencies(User $user): bool
    {
        $documents = $user->idDocuments()->approved()->get();

        if ($documents->count() < 2) {
            return false;
        }

        // Check for name inconsistencies across documents
        $names = $documents->pluck('ocr_text')->filter()->map(function ($text) {
            // Extract name from OCR text (simplified)
            return strtolower(trim($text));
        })->unique();

        return $names->count() > 1;
    }

    /**
     * Generate regulatory report
     */
    public function generateRegulatoryReport(string $reportType, Carbon $startDate, Carbon $endDate): RegulatoryReport
    {
        $reportData = $this->gatherReportData($reportType, $startDate, $endDate);

        return DB::transaction(function () use ($reportType, $startDate, $endDate, $reportData) {
            return RegulatoryReport::create([
                'report_type' => $reportType,
                'report_date' => now(),
                'period_start' => $startDate,
                'period_end' => $endDate,
                'report_data' => json_encode($reportData),
                'generated_by' => auth()->id() ?? 1, // Fallback to admin user
            ]);
        });
    }

    /**
     * Gather data for regulatory report
     */
    private function gatherReportData(string $reportType, Carbon $startDate, Carbon $endDate): array
    {
        switch ($reportType) {
            case 'aml_summary':
                return $this->gatherAMLSummaryData($startDate, $endDate);
            case 'kyc_completion':
                return $this->gatherKYCCompletionData($startDate, $endDate);
            case 'risk_assessment':
                return $this->gatherRiskAssessmentData($startDate, $endDate);
            default:
                return ['error' => 'Unknown report type'];
        }
    }

    /**
     * Gather AML summary data
     */
    private function gatherAMLSummaryData(Carbon $startDate, Carbon $endDate): array
    {
        $screenings = User::whereBetween('aml_screened_at', [$startDate, $endDate])->get();

        $riskLevels = $screenings->groupBy(function ($user) {
            $results = json_decode($user->aml_results, true);
            return $results['risk_level'] ?? 'unknown';
        });

        return [
            'total_screenings' => $screenings->count(),
            'risk_distribution' => $riskLevels->map->count(),
            'high_risk_cases' => $riskLevels->get('high', collect())->count(),
            'flagged_cases' => User::where('compliance_status', 'flagged')
                ->whereBetween('aml_screened_at', [$startDate, $endDate])
                ->count()
        ];
    }

    /**
     * Gather KYC completion data
     */
    private function gatherKYCCompletionData(Carbon $startDate, Carbon $endDate): array
    {
        $totalUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $completedKYC = User::where('status', 'approved')
            ->whereBetween('approved_at', [$startDate, $endDate])
            ->count();

        return [
            'total_registrations' => $totalUsers,
            'kyc_completed' => $completedKYC,
            'completion_rate' => $totalUsers > 0 ? round(($completedKYC / $totalUsers) * 100, 2) : 0,
            'pending_kyc' => User::whereIn('status', ['pending', 'review'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count()
        ];
    }

    /**
     * Gather risk assessment data
     */
    private function gatherRiskAssessmentData(Carbon $startDate, Carbon $endDate): array
    {
        $assessments = User::whereBetween('risk_assessed_at', [$startDate, $endDate])->get();

        $riskLevels = $assessments->groupBy('risk_level');

        return [
            'total_assessments' => $assessments->count(),
            'risk_distribution' => $riskLevels->map->count(),
            'average_risk_score' => $assessments->avg('risk_score') ?? 0,
            'high_risk_users' => $assessments->where('risk_level', 'high')->count()
        ];
    }

    /**
     * Log compliance event
     */
    private function logComplianceEvent(User $user, string $eventType, string $eventSubtype, array $eventData = null): void
    {
        try {
            ComplianceLog::create([
                'user_id' => $user->id,
                'event_type' => $eventType,
                'event_subtype' => $eventSubtype,
                'event_data' => $eventData ? json_encode($eventData) : null,
                'performed_by' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log compliance event', [
                'user_id' => $user->id,
                'event_type' => $eventType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get compliance statistics
     */
    public function getComplianceStats(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $query = User::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString()
            ],
            'aml_screened' => (clone $query)->where('aml_screened', true)->count(),
            'kyc_completed' => (clone $query)->where('status', 'approved')->count(),
            'high_risk_users' => (clone $query)->where('risk_level', 'high')->count(),
            'flagged_users' => (clone $query)->where('compliance_status', 'flagged')->count(),
            'completion_rate' => [
                'aml' => $this->calculateCompletionRate((clone $query)->where('aml_screened', true)->count(), $query->count()),
                'kyc' => $this->calculateCompletionRate((clone $query)->where('status', 'approved')->count(), $query->count())
            ]
        ];
    }

    /**
     * Calculate completion rate
     */
    private function calculateCompletionRate(int $completed, int $total): float
    {
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0.0;
    }
}