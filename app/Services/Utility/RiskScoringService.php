<?php

namespace App\Services\Utility;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RiskScoringService
{
    /**
     * Risk factors and their weights
     */
    private const RISK_FACTORS = [
        'incomplete_verification' => ['weight' => 0.3, 'description' => 'Missing identity verification'],
        'document_rejection' => ['weight' => 0.25, 'description' => 'Previous document rejections'],
        'rapid_registration' => ['weight' => 0.15, 'description' => 'Rapid account creation and activity'],
        'international_activity' => ['weight' => 0.1, 'description' => 'International account indicators'],
        'suspicious_behavior' => ['weight' => 0.2, 'description' => 'Suspicious account behavior'],
        'face_match_failure' => ['weight' => 0.4, 'description' => 'Face verification failed'],
        'address_unverified' => ['weight' => 0.15, 'description' => 'Address not verified'],
        'background_check_flags' => ['weight' => 0.35, 'description' => 'Background check concerns'],
        'compliance_flags' => ['weight' => 0.3, 'description' => 'AML/KYC compliance issues']
    ];

    /**
     * Risk level thresholds
     */
    private const RISK_THRESHOLDS = [
        'low' => 0.2,
        'medium' => 0.4,
        'high' => 0.7,
        'critical' => 0.9
    ];

    /**
     * Calculate comprehensive risk score for a user
     */
    public function calculateRiskScore(User $user): array
    {
        try {
            $riskFactors = [];
            $totalScore = 0.0;
            $maxPossibleScore = 0.0;

            // Evaluate each risk factor
            foreach (self::RISK_FACTORS as $factor => $config) {
                $factorScore = $this->evaluateRiskFactor($user, $factor);
                $weightedScore = $factorScore * $config['weight'];

                $riskFactors[$factor] = [
                    'score' => $factorScore,
                    'weight' => $config['weight'],
                    'weighted_score' => $weightedScore,
                    'description' => $config['description'],
                    'triggered' => $factorScore > 0
                ];

                $totalScore += $weightedScore;
                $maxPossibleScore += $config['weight'];
            }

            // Normalize score to 0-1 range
            $normalizedScore = $maxPossibleScore > 0 ? $totalScore / $maxPossibleScore : 0.0;

            // Determine risk level
            $riskLevel = $this->determineRiskLevel($normalizedScore);

            $result = [
                'score' => round($normalizedScore, 4),
                'level' => $riskLevel,
                'factors' => $riskFactors,
                'total_weighted_score' => round($totalScore, 4),
                'max_possible_score' => round($maxPossibleScore, 4),
                'calculated_at' => now(),
                'assessment_method' => 'automated'
            ];

            // Update user record
            $user->update([
                'risk_score' => $normalizedScore,
                'risk_factors' => json_encode($riskFactors),
                'risk_level' => $riskLevel,
                'risk_assessed_at' => now()
            ]);

            // Log risk assessment
            $this->logRiskAssessment($user, $result);

            return $result;

        } catch (\Exception $e) {
            Log::error('Risk score calculation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'score' => 0.5, // Default medium risk
                'level' => 'medium',
                'error' => 'Risk calculation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Evaluate individual risk factor
     */
    private function evaluateRiskFactor(User $user, string $factor): float
    {
        switch ($factor) {
            case 'incomplete_verification':
                return $this->evaluateIncompleteVerification($user);

            case 'document_rejection':
                return $this->evaluateDocumentRejections($user);

            case 'rapid_registration':
                return $this->evaluateRapidRegistration($user);

            case 'international_activity':
                return $this->evaluateInternationalActivity($user);

            case 'suspicious_behavior':
                return $this->evaluateSuspiciousBehavior($user);

            case 'face_match_failure':
                return $this->evaluateFaceMatchFailure($user);

            case 'address_unverified':
                return $this->evaluateAddressUnverified($user);

            case 'background_check_flags':
                return $this->evaluateBackgroundCheckFlags($user);

            case 'compliance_flags':
                return $this->evaluateComplianceFlags($user);

            default:
                return 0.0;
        }
    }

    /**
     * Evaluate incomplete verification risk
     */
    private function evaluateIncompleteVerification(User $user): float
    {
        if ($user->hasVerifiedDocuments()) {
            return 0.0;
        }

        // Higher risk for users pending longer
        $daysPending = $user->created_at->diffInDays(now());
        if ($daysPending > 30) {
            return 1.0;
        } elseif ($daysPending > 7) {
            return 0.7;
        }

        return 0.5;
    }

    /**
     * Evaluate document rejection history
     */
    private function evaluateDocumentRejections(User $user): float
    {
        $rejectedCount = $user->idDocuments()->rejected()->count();
        $totalDocuments = $user->idDocuments()->count();

        if ($totalDocuments === 0) {
            return 0.0;
        }

        $rejectionRate = $rejectedCount / $totalDocuments;

        if ($rejectionRate > 0.5) {
            return 1.0;
        } elseif ($rejectionRate > 0.25) {
            return 0.7;
        } elseif ($rejectedCount > 0) {
            return 0.4;
        }

        return 0.0;
    }

    /**
     * Evaluate rapid registration and activity
     */
    private function evaluateRapidRegistration(User $user): float
    {
        $hoursSinceCreation = $user->created_at->diffInHours(now());
        $documentsCount = $user->idDocuments()->count();

        // Flag if many documents uploaded quickly
        if ($hoursSinceCreation < 24 && $documentsCount > 2) {
            return 0.8;
        } elseif ($hoursSinceCreation < 48 && $documentsCount > 3) {
            return 0.6;
        }

        return 0.0;
    }

    /**
     * Evaluate international activity indicators
     */
    private function evaluateInternationalActivity(User $user): float
    {
        // Placeholder - in production, check IP geolocation, phone codes, etc.
        // For now, return low risk
        return 0.1;
    }

    /**
     * Evaluate suspicious behavior patterns
     */
    private function evaluateSuspiciousBehavior(User $user): float
    {
        $score = 0.0;

        // Check verification attempts
        if ($user->verification_attempts > 5) {
            $score += 0.3;
        }

        // Check for recent failed attempts
        if ($user->last_verification_attempt && 
            \Carbon\Carbon::parse($user->last_verification_attempt)->diffInHours(now()) < 24) {
            $score += 0.2;
        }

        return min($score, 1.0);
    }

    /**
     * Evaluate face match failure
     */
    private function evaluateFaceMatchFailure(User $user): float
    {
        if (!$user->face_match_score) {
            return 0.5; // Unknown, assume medium risk
        }

        $matchScore = $user->face_match_score;
        if ($matchScore < 0.3) {
            return 1.0; // Very low match
        } elseif ($matchScore < 0.6) {
            return 0.7; // Low match
        }

        return 0.0; // Good match
    }

    /**
     * Evaluate unverified address
     */
    private function evaluateAddressUnverified(User $user): float
    {
        return $user->address_verified ? 0.0 : 0.6;
    }

    /**
     * Evaluate background check flags
     */
    private function evaluateBackgroundCheckFlags(User $user): float
    {
        if (!$user->background_check_completed) {
            return 0.3; // Not completed yet
        }

        if ($user->background_check_status === 'failed') {
            return 1.0;
        }

        // Check results for flags (simplified)
        $results = is_string($user->background_check_results) ? json_decode($user->background_check_results, true) : null;
        if ($results && isset($results['flags']) && count($results['flags']) > 0) {
            return 0.8;
        }

        return 0.0;
    }

    /**
     * Evaluate compliance flags
     */
    private function evaluateComplianceFlags(User $user): float
    {
        if ($user->compliance_status === 'flagged') {
            return 1.0;
        } elseif ($user->compliance_status === 'review_required') {
            return 0.6;
        }

        return 0.0;
    }

    /**
     * Determine risk level based on score
     */
    private function determineRiskLevel(float $score): string
    {
        if ($score >= self::RISK_THRESHOLDS['critical']) {
            return 'critical';
        } elseif ($score >= self::RISK_THRESHOLDS['high']) {
            return 'high';
        } elseif ($score >= self::RISK_THRESHOLDS['medium']) {
            return 'medium';
        } elseif ($score >= self::RISK_THRESHOLDS['low']) {
            return 'low';
        }

        return 'clear';
    }

    /**
     * Log risk assessment
     */
    private function logRiskAssessment(User $user, array $result): void
    {
        // TODO: Implement logging via ComplianceService once it's fully integrated
        Log::info('Risk assessment completed', [
            'user_id' => $user->id,
            'score' => $result['score'],
            'level' => $result['level']
        ]);
    }

    /**
     * Get risk statistics
     */
    public function getRiskStatistics(): array
    {
        $users = User::all();

        $riskLevels = $users->groupBy('risk_level');
        $averageScore = $users->avg('risk_score') ?? 0;

        $factorStats = [];
        foreach (self::RISK_FACTORS as $factor => $config) {
            $triggeredCount = $users->filter(function ($user) use ($factor) {
                $factors = json_decode($user->risk_factors, true);
                return $factors && isset($factors[$factor]) && $factors[$factor]['triggered'];
            })->count();

            $factorStats[$factor] = [
                'triggered_count' => $triggeredCount,
                'trigger_rate' => $users->count() > 0 ? round(($triggeredCount / $users->count()) * 100, 2) : 0,
                'description' => $config['description']
            ];
        }

        return [
            'total_users' => $users->count(),
            'average_risk_score' => round($averageScore, 4),
            'risk_distribution' => $riskLevels->map(fn($group) => $group->count())->toArray(),
            'factor_statistics' => $factorStats,
            'high_risk_users' => $users->where('risk_level', 'high')->count(),
            'critical_risk_users' => $users->where('risk_level', 'critical')->count()
        ];
    }

    /**
     * Batch assess risks for multiple users
     */
    public function batchAssessRisks(array $userIds): array
    {
        $results = [];

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $results[$userId] = $this->calculateRiskScore($user);
            }
        }

        return $results;
    }

    /**
     * Get risk recommendations for a user
     */
    public function getRiskRecommendations(User $user): array
    {
        $recommendations = [];
        $factors = json_decode($user->risk_factors, true) ?? [];

        foreach ($factors as $factor => $data) {
            if ($data['triggered']) {
                $recommendations = array_merge($recommendations,
                    $this->getRecommendationsForFactor($factor, $data['score'])
                );
            }
        }

        return array_unique($recommendations);
    }

    /**
     * Get recommendations for specific risk factor
     */
    private function getRecommendationsForFactor(string $factor, float $score): array
    {
        $recommendations = [];

        switch ($factor) {
            case 'incomplete_verification':
                $recommendations[] = 'Complete identity verification with valid documents';
                $recommendations[] = 'Upload additional proof of identity';
                break;

            case 'face_match_failure':
                $recommendations[] = 'Retake profile photo ensuring good lighting and clear face';
                $recommendations[] = 'Ensure ID document photo shows full face clearly';
                break;

            case 'address_unverified':
                $recommendations[] = 'Verify residential address with utility bill or bank statement';
                break;

            case 'background_check_flags':
                $recommendations[] = 'Contact support to review background check results';
                break;

            case 'compliance_flags':
                $recommendations[] = 'Complete enhanced due diligence requirements';
                break;
        }

        return $recommendations;
    }
}
