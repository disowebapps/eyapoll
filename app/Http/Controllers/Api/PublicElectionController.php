<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Election\Election;
use App\Services\Election\ElectionTransparencyService;
use App\Services\Election\ElectionFairnessService;
use Illuminate\Http\JsonResponse;

class PublicElectionController extends Controller
{
    public function getPublicResults(Election $election): JsonResponse
    {
        if (!$election->results_published) {
            return response()->json(['error' => 'Results not yet published'], 403);
        }

        $transparencyService = app(ElectionTransparencyService::class);

        return response()->json([
            'election' => [
                'title' => $election->title,
                'status' => $election->status->value,
                'ended_at' => $election->ends_at->toISOString()
            ],
            'results' => $this->getPublicResultsData($election),
            'transparency' => $transparencyService->generatePublicAuditTrail($election),
            'verification_url' => route('public.election.verify', $election->id)
        ]);
    }

    public function verifyElectionIntegrity(Election $election): JsonResponse
    {
        $integrityService = app(\App\Services\Election\ElectionIntegrityService::class);
        $fairnessService = app(ElectionFairnessService::class);
        
        return response()->json([
            'integrity_check' => $integrityService->verifyElectionIntegrity($election),
            'fairness_analysis' => $fairnessService->analyzeElectionFairness($election),
            'verified_at' => now()->toISOString(),
            'public_verification_hash' => hash('sha256', $election->id . now()->format('Y-m-d'))
        ]);
    }

    public function getAnonymizedVoteProofs(Election $election): JsonResponse
    {
        if (!$election->results_published) {
            return response()->json(['error' => 'Results not yet published'], 403);
        }

        $transparencyService = app(ElectionTransparencyService::class);
        
        return response()->json([
            'vote_proofs' => $transparencyService->getAnonymizedVoteProofs($election),
            'total_votes' => $election->votes()->count(),
            'verification_note' => 'These are anonymized vote receipts for public verification'
        ]);
    }

    private function getPublicResultsData(Election $election): array
    {
        return $election->positions()->with(['voteTallies.candidate.user'])->get()->map(function($position) {
            $tallies = $position->voteTallies()->orderBy('vote_count', 'desc')->get();
            $totalVotes = $tallies->sum('vote_count');
            
            return [
                'position' => $position->title,
                'total_votes' => $totalVotes,
                'candidates' => $tallies->map(function($tally) use ($totalVotes) {
                    return [
                        'name' => $tally->candidate?->user?->full_name ?? 'Unknown',
                        'votes' => $tally->vote_count,
                        'percentage' => $totalVotes > 0 ? round(($tally->vote_count / $totalVotes) * 100, 2) : 0,
                        'ranking' => $tally->getRanking()
                    ];
                })
            ];
        })->toArray();
    }
}