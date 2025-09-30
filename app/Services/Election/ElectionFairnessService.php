<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Models\Voting\VoteRecord;
use App\Models\Voting\VoteTally;
use Illuminate\Support\Facades\DB;

class ElectionFairnessService
{
    public function analyzeElectionFairness(Election $election): array
    {
        return [
            'vote_distribution_analysis' => $this->analyzeVoteDistribution($election),
            'temporal_voting_patterns' => $this->analyzeTemporalPatterns($election),
            'statistical_anomalies' => $this->detectAnomalies($election),
            'fairness_score' => $this->calculateFairnessScore($election),
            'recommendations' => $this->generateRecommendations($election)
        ];
    }

    private function analyzeVoteDistribution(Election $election): array
    {
        $tallies = VoteTally::where('election_id', $election->id)
            ->with('candidate')
            ->get();

        $totalVotes = $tallies->sum('vote_count');
        $candidateCount = $tallies->count();
        
        if ($candidateCount === 0) return ['status' => 'no_candidates'];

        $expectedVoteShare = $totalVotes / $candidateCount;
        $deviations = $tallies->map(function($tally) use ($expectedVoteShare) {
            return abs($tally->vote_count - $expectedVoteShare);
        });

        return [
            'total_votes' => $totalVotes,
            'candidate_count' => $candidateCount,
            'expected_vote_share' => round($expectedVoteShare, 2),
            'max_deviation' => $deviations->max(),
            'avg_deviation' => round($deviations->avg(), 2),
            'distribution_fairness' => $this->calculateDistributionFairness($deviations, $expectedVoteShare)
        ];
    }

    private function analyzeTemporalPatterns(Election $election): array
    {
        $votesByHour = VoteRecord::where('election_id', $election->id)
            ->selectRaw('HOUR(cast_at) as hour, COUNT(*) as votes')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('votes', 'hour')
            ->toArray();

        $totalHours = count($votesByHour);
        if ($totalHours === 0) return ['status' => 'no_votes'];

        $avgVotesPerHour = array_sum($votesByHour) / $totalHours;
        $hourlyDeviations = array_map(fn($votes) => abs($votes - $avgVotesPerHour), $votesByHour);

        return [
            'votes_by_hour' => $votesByHour,
            'avg_votes_per_hour' => round($avgVotesPerHour, 2),
            'max_hourly_deviation' => max($hourlyDeviations),
            'temporal_consistency' => $this->calculateTemporalConsistency($hourlyDeviations, $avgVotesPerHour),
            'peak_voting_hours' => $this->identifyPeakHours($votesByHour)
        ];
    }

    private function detectAnomalies(Election $election): array
    {
        $anomalies = [];

        // Check for suspicious voting patterns
        $rapidVotes = VoteRecord::where('election_id', $election->id)
            ->selectRaw('COUNT(*) as count, MINUTE(cast_at) as minute')
            ->groupBy('minute')
            ->having('count', '>', 10)
            ->get();

        if ($rapidVotes->count() > 0) {
            $anomalies[] = [
                'type' => 'rapid_voting',
                'description' => 'Unusually high voting activity detected in specific minutes',
                'severity' => 'medium',
                'affected_periods' => $rapidVotes->pluck('minute')->toArray()
            ];
        }

        // Check for vote timing anomalies
        $voteGaps = DB::select("
            SELECT 
                TIMESTAMPDIFF(SECOND, LAG(cast_at) OVER (ORDER BY cast_at), cast_at) as gap_seconds
            FROM votes 
            WHERE election_id = ? 
            ORDER BY cast_at
        ", [$election->id]);

        $largeGaps = array_filter($voteGaps, fn($gap) => $gap->gap_seconds > 3600); // 1 hour gaps
        
        if (count($largeGaps) > 0) {
            $anomalies[] = [
                'type' => 'voting_gaps',
                'description' => 'Large time gaps between votes detected',
                'severity' => 'low',
                'gap_count' => count($largeGaps)
            ];
        }

        return $anomalies;
    }

    private function calculateFairnessScore(Election $election): array
    {
        $distribution = $this->analyzeVoteDistribution($election);
        $temporal = $this->analyzeTemporalPatterns($election);
        $anomalies = $this->detectAnomalies($election);

        $distributionScore = $distribution['distribution_fairness'] ?? 0;
        $temporalScore = $temporal['temporal_consistency'] ?? 0;
        $anomalyPenalty = count($anomalies) * 10;

        $overallScore = max(0, min(100, ($distributionScore + $temporalScore) / 2 - $anomalyPenalty));

        return [
            'overall_score' => round($overallScore, 1),
            'distribution_score' => round($distributionScore, 1),
            'temporal_score' => round($temporalScore, 1),
            'anomaly_penalty' => $anomalyPenalty,
            'grade' => $this->getScoreGrade($overallScore),
            'assessment' => $this->getScoreAssessment($overallScore)
        ];
    }

    private function calculateDistributionFairness(object $deviations, float $expected): float
    {
        if ($expected == 0) return 100;
        $avgDeviation = $deviations->avg();
        $fairnessRatio = 1 - ($avgDeviation / $expected);
        return max(0, min(100, $fairnessRatio * 100));
    }

    private function calculateTemporalConsistency(array $deviations, float $avgVotes): float
    {
        if ($avgVotes == 0) return 100;
        $avgDeviation = array_sum($deviations) / count($deviations);
        $consistencyRatio = 1 - ($avgDeviation / $avgVotes);
        return max(0, min(100, $consistencyRatio * 100));
    }

    private function identifyPeakHours(array $votesByHour): array
    {
        $maxVotes = max($votesByHour);
        return array_keys(array_filter($votesByHour, fn($votes) => $votes >= $maxVotes * 0.8));
    }

    private function getScoreGrade(float $score): string
    {
        return match(true) {
            $score >= 90 => 'A+',
            $score >= 80 => 'A',
            $score >= 70 => 'B',
            $score >= 60 => 'C',
            default => 'D'
        };
    }

    private function getScoreAssessment(float $score): string
    {
        return match(true) {
            $score >= 90 => 'Excellent fairness - Election conducted with highest integrity',
            $score >= 80 => 'Good fairness - Minor irregularities detected',
            $score >= 70 => 'Acceptable fairness - Some concerns identified',
            $score >= 60 => 'Fair - Multiple issues require attention',
            default => 'Poor fairness - Significant irregularities detected'
        };
    }

    private function generateRecommendations(Election $election): array
    {
        $fairness = $this->calculateFairnessScore($election);
        $recommendations = [];

        if ($fairness['overall_score'] < 80) {
            $recommendations[] = 'Consider implementing additional vote verification measures';
        }

        if ($fairness['temporal_score'] < 70) {
            $recommendations[] = 'Review voting period distribution for better accessibility';
        }

        if ($fairness['distribution_score'] < 70) {
            $recommendations[] = 'Analyze candidate visibility and campaign fairness';
        }

        return $recommendations;
    }
}