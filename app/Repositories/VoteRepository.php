<?php

namespace App\Repositories;

use App\Models\Voting\VoteRecord;
use Illuminate\Database\Eloquent\Collection;

class VoteRepository extends BaseRepository
{
    public function __construct(VoteRecord $model)
    {
        parent::__construct($model);
    }

    public function findByVoterHash(string $voterHash): Collection
    {
        return $this->model->where('voter_hash', $voterHash)->get();
    }

    public function findByElection(int $electionId): Collection
    {
        return $this->model->where('election_id', $electionId)->get();
    }

    public function findByReceiptHash(string $receiptHash): ?VoteRecord
    {
        return $this->model->where('receipt_hash', $receiptHash)->first();
    }

    public function countByElection(int $electionId): int
    {
        return $this->model->where('election_id', $electionId)->count();
    }

    public function getVoteStats(int $electionId): array
    {
        return [
            'total_votes' => $this->countByElection($electionId),
            'unique_voters' => $this->model->where('election_id', $electionId)
                ->distinct('voter_hash')
                ->count('voter_hash'),
            'votes_today' => $this->model->where('election_id', $electionId)
                ->whereDate('cast_at', today())
                ->count(),
            'votes_this_week' => $this->model->where('election_id', $electionId)
                ->whereBetween('cast_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];
    }

    public function hasUserVotedInElection(string $voterHash, int $electionId): bool
    {
        return $this->model->where('voter_hash', $voterHash)
            ->where('election_id', $electionId)
            ->exists();
    }

    public function getUserVoteForElection(string $voterHash, int $electionId): ?VoteRecord
    {
        return $this->model->where('voter_hash', $voterHash)
            ->where('election_id', $electionId)
            ->first();
    }
}