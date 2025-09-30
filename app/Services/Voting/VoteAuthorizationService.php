<?php

namespace App\Services\Voting;

use App\Models\User;
use App\Models\Election\Election;
use App\Models\Voting\VoteAuthorization;
use App\Models\Voting\VoteRecord;
use App\Services\Audit\AuditService;
use App\Services\Voting\VoterHashService;
use App\Services\Voting\EligibilityService;
use App\Services\Election\ElectionTimeService;

class VoteAuthorizationService
{
    public function __construct(
        private AuditService $auditService,
        private VoterHashService $hashService,
        private EligibilityService $eligibilityService
    ) {}

    public function authorizeVote(User $user, Election $election): array
    {
        // 1. Real-time eligibility check
        $eligibility = $this->eligibilityService->checkEligibility($user, $election);
        if (!$eligibility->isEligible()) {
            $this->auditService->logVoteAttempt($user, $election, 'denied', $eligibility->getReasons());
            return ['status' => 'denied', 'reasons' => $eligibility->getReasons()];
        }

        // 2. Check if already voted
        $voterHash = $this->hashService->generateVoterHash($user, $election);
        if ($this->hasAlreadyVoted($voterHash, $election->id)) {
            $this->auditService->logVoteAttempt($user, $election, 'already_voted');
            return [
                'status' => 'denied', 
                'reasons' => ["You have already voted in this election."],
                'receipt_url' => route('voter.receipt', ['election' => $election->id])
            ];
        }

        // 3. Generate secure authorization
        $authorization = $this->generateAuthorization($user, $election, $eligibility);

        // 4. Log authorization
        $this->auditService->logVoteAuthorization($user, $election, $authorization);

        return ['status' => 'authorized', 'authorization' => $authorization];
    }

    private function generateAuthorization(User $user, Election $election, $eligibility): VoteAuthorization
    {
        $voterHash = $this->hashService->generateVoterHash($user, $election);
        $timeout = $this->calculateTimeout($election);
        $currentTime = app(ElectionTimeService::class)->getCurrentTime();

        return VoteAuthorization::create([
            'voter_hash' => $voterHash,
            'election_id' => $election->id,
            'auth_token' => VoteAuthorization::generateSecureToken($voterHash, $election->id),
            'expires_at' => $currentTime->addMinutes($timeout),
            'initial_timeout_minutes' => $timeout,
            'last_activity_at' => $currentTime,
            'eligibility_snapshot' => $eligibility->toArray()
        ]);
    }

    private function calculateTimeout(Election $election): int
    {
        return 30; // 30 minutes base timeout
    }

    private function hasAlreadyVoted(string $voterHash, int $electionId): bool
    {
        return VoteRecord::where('voter_hash', $voterHash)
            ->where('election_id', $electionId)
            ->exists();
    }

    public function handleExpiredAuthorization(string $voterHash, int $electionId): array
    {
        // Find user by hash (for recovery)
        $user = $this->hashService->getUserByHash($voterHash, $electionId);
        if (!$user) {
            return ['status' => 'cannot_recover', 'reason' => 'User not found'];
        }

        // Check if still eligible
        $election = Election::find($electionId);
        $eligibility = $this->eligibilityService->checkEligibility($user, $election);
        if (!$eligibility->isEligible()) {
            return ['status' => 'cannot_recover', 'reason' => 'No longer eligible'];
        }

        // Check if voted in the meantime
        if ($this->hasAlreadyVoted($voterHash, $electionId)) {
            return ['status' => 'cannot_recover', 'reason' => 'Already voted'];
        }

        // Generate new authorization
        $newAuth = $this->generateAuthorization($user, $election, $eligibility);

        // Restore draft if exists
        $draft = app(BallotDraftService::class)->restoreDraft($voterHash, $electionId);

        $this->auditService->logAuthorizationRecovery($user, $election, $newAuth);

        return [
            'status' => 'recovered',
            'authorization' => $newAuth,
            'draft' => $draft
        ];
    }
}