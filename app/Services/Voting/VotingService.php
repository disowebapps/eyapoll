<?php

namespace App\Services\Voting;

use App\Models\Voting\VoteAuthorization;
use App\Models\Voting\VoteRecord;
use App\Repositories\VoteRepository;
use App\Repositories\VoteTallyRepository;
use App\Services\RetryService;
use App\Services\LoggingService;
use App\Exceptions\VotingException;
use App\Exceptions\DatabaseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class VotingService
{
    private VoteRepository $voteRepository;
    private VoteTallyRepository $tallyRepository;

    public function __construct(
        VoteRepository $voteRepository,
        VoteTallyRepository $tallyRepository
    ) {
        $this->voteRepository = $voteRepository;
        $this->tallyRepository = $tallyRepository;
    }

    /**
     * Cast a vote with proper validation and error handling
     */
    public function castVote(VoteAuthorization $authorization, array $selections): array
    {
        $startTime = LoggingService::startTimer();

        LoggingService::logVotingEvent('vote_cast_initiated', $authorization->election_id, [
            'positions_count' => count($selections),
            'voter_hash' => substr($authorization->voter_hash, 0, 8) . '...' // Partial hash for privacy
        ]);

        try {
            return RetryService::retryDatabase(function () use ($authorization, $selections, $startTime) {
                return DB::transaction(function () use ($authorization, $selections, $startTime) {
                    // Validate vote hasn't been cast already
                    if ($this->hasUserVoted($authorization->voter_hash, $authorization->election_id)) {
                        throw new VotingException('Vote has already been cast for this election');
                    }

                    // Enforce single candidate per position
                    $this->validateSingleCandidatePerPosition($selections);

                    // Generate receipt hash
                    $receiptHash = $this->generateReceiptHash($selections);

                    // Create vote record
                    $voteRecord = $this->createVoteRecord($authorization, $selections, $receiptHash);

                    // Update vote tallies
                    $this->updateVoteTallies($authorization->election_id, $selections);

                    // Mark authorization as used
                    $this->markAuthorizationUsed($authorization);

                    LoggingService::endTimer($startTime, 'vote_cast', [
                        'election_id' => $authorization->election_id,
                        'receipt_hash' => substr($receiptHash, 0, 8) . '...',
                        'positions_voted' => count($selections)
                    ]);

                    LoggingService::logVotingEvent('vote_cast_successful', $authorization->election_id, [
                        'receipt_hash' => substr($receiptHash, 0, 8) . '...',
                        'positions_voted' => count($selections)
                    ]);

                    // Clear voter dashboard cache to refresh vote status
                    $this->clearVoterCache($authorization->voter_hash);

                    return [
                        'status' => 'success',
                        'receipt' => [
                            'receipt_hash' => $receiptHash,
                            'cast_at' => $voteRecord->cast_at
                        ]
                    ];
                });
            }, 2);
        } catch (VotingException $e) {
            LoggingService::endTimer($startTime, 'vote_cast_validation_failed');
            LoggingService::logVotingEvent('vote_cast_validation_failed', $authorization->election_id, [
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (Exception $e) {
            LoggingService::endTimer($startTime, 'vote_cast_failed');
            LoggingService::logVotingEvent('vote_cast_failed', $authorization->election_id, [
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ]);
            throw new DatabaseException('Failed to cast vote due to database error', [], 0, $e);
        }
    }

    /**
     * Check if user has already voted in election
     */
    private function hasUserVoted(string $voterHash, int $electionId): bool
    {
        return $this->voteRepository->hasUserVotedInElection($voterHash, $electionId);
    }

    /**
     * Create vote record in database
     */
    private function createVoteRecord(VoteAuthorization $authorization, array $selections, string $receiptHash)
    {
        return $this->voteRepository->create([
            'voter_hash' => $authorization->voter_hash,
            'election_id' => $authorization->election_id,
            'encrypted_selections' => encrypt($selections),
            'receipt_hash' => $receiptHash,
            'cast_at' => now()
        ]);
    }

    /**
     * Mark vote authorization as used
     */
    private function markAuthorizationUsed(VoteAuthorization $authorization): void
    {
        $authorization->update([
            'is_used' => true,
            'used_at' => now()
        ]);
    }

    private function generateReceiptHash(array $selections): string
    {
        return hash('sha256', json_encode($selections) . now()->timestamp . random_bytes(16));
    }

    /**
     * Validate that only one candidate is selected per position
     */
    private function validateSingleCandidatePerPosition(array $selections): void
    {
        foreach ($selections as $positionId => $candidateIds) {
            if (!is_array($candidateIds)) {
                $candidateIds = [$candidateIds];
            }
            
            if (count($candidateIds) > 1) {
                throw new VotingException('Only one candidate can be selected per position');
            }
        }
    }

    /**
     * Update vote tallies for all selections
     */
    private function updateVoteTallies(int $electionId, array $selections): void
    {
        foreach ($selections as $positionId => $candidateIds) {
            if (!is_array($candidateIds)) {
                $candidateIds = [$candidateIds];
            }

            foreach ($candidateIds as $candidateId) {
                $this->tallyRepository->incrementTally($electionId, $positionId, $candidateId);
            }
        }

        Log::debug('Vote tallies updated', [
            'election_id' => $electionId,
            'positions_count' => count($selections)
        ]);
    }

    /**
     * Get voter receipt for election
     */
    public function getVoterReceipt(string $voterHash, $election): ?array
    {
        $electionId = is_object($election) ? $election->id : $election;

        Log::info('Looking for receipt', [
            'voter_hash' => $voterHash,
            'election_id' => $electionId
        ]);

        $voteRecord = $this->voteRepository->getUserVoteForElection($voterHash, $electionId);

        if (!$voteRecord) {
            Log::info('No vote record found for receipt lookup');
            return null;
        }

        // Get election details
        $electionModel = is_object($election) ? $election : \App\Models\Election\Election::find($electionId);

        Log::info('Receipt found', ['receipt_hash' => $voteRecord->receipt_hash]);
        return [
            'receipt_hash' => $voteRecord->receipt_hash,
            'short_receipt_hash' => substr($voteRecord->receipt_hash, 0, 8),
            'vote_hash' => $voteRecord->voter_hash,
            'cast_at' => $voteRecord->cast_at,
            'election_id' => $voteRecord->election_id,
            'election_title' => $electionModel->title,
            'position_title' => 'Vote Cast Successfully',
            'selections' => [],
            'chain_position' => $voteRecord->id,
            'verification_code' => substr($voteRecord->receipt_hash, -6),
            'status' => 'verified',
            'is_verifiable' => true
        ];
    }

    /**
     * Verify vote receipt
     */
    public function verifyReceipt(string $receiptHash): ?array
    {
        Log::info('Verifying receipt', ['receipt_hash' => $receiptHash]);

        $voteRecord = $this->voteRepository->findByReceiptHash($receiptHash);

        if (!$voteRecord) {
            Log::info('No vote record found for receipt verification');
            return null;
        }

        $election = \App\Models\Election\Election::find($voteRecord->election_id);

        Log::info('Receipt verified', ['receipt_hash' => $receiptHash]);
        return [
            'receipt_hash' => $voteRecord->receipt_hash,
            'short_receipt_hash' => substr($voteRecord->receipt_hash, 0, 8),
            'vote_hash' => $voteRecord->voter_hash,
            'cast_at' => $voteRecord->cast_at,
            'election_id' => $voteRecord->election_id,
            'election_title' => $election->title,
            'position_title' => 'Vote Verified Successfully',
            'selections' => [],
            'chain_position' => $voteRecord->id,
            'verification_code' => substr($voteRecord->receipt_hash, -6),
            'status' => 'verified',
            'is_verifiable' => true,
            'valid' => true,
            'message' => 'Receipt verified successfully'
        ];
    }

    /**
     * Clear voter cache after voting
     */
    private function clearVoterCache(string $voterHash): void
    {
        try {
            // Extract user ID from voter hash pattern
            if (preg_match('/^(\d+)_/', $voterHash, $matches)) {
                $userId = $matches[1];
                Cache::forget("user_dashboard_{$userId}_4");
                Cache::forget("user_dashboard_{$userId}_10");
                Cache::forget("user_vote_records_{$userId}");
                Cache::forget("user_recent_vote_history_{$userId}_4");
                Cache::forget("user_elections_{$userId}_10_all");
            }
        } catch (Exception $e) {
            Log::warning('Failed to clear voter cache', ['error' => $e->getMessage()]);
        }
    }
}