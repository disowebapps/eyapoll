<?php

namespace App\Repositories;

use App\Models\Voting\VoteTally;
use Illuminate\Support\Collection;

class VoteTallyRepository extends BaseRepository
{
    public function __construct(VoteTally $model)
    {
        parent::__construct($model);
    }

    public function getTallyForElection(int $electionId): Collection
    {
        return $this->model->where('election_id', $electionId)
            ->selectRaw('position_id, candidate_id, COUNT(*) as vote_count')
            ->groupBy('position_id', 'candidate_id')
            ->orderBy('position_id')
            ->orderByDesc('vote_count')
            ->get();
    }

    public function getTallyForPosition(int $electionId, int $positionId): Collection
    {
        return $this->model->where('election_id', $electionId)
            ->where('position_id', $positionId)
            ->selectRaw('candidate_id, COUNT(*) as vote_count')
            ->groupBy('candidate_id')
            ->orderByDesc('vote_count')
            ->get();
    }

    public function incrementTally(int $electionId, int $positionId, int $candidateId): void
    {
        $this->model->create([
            'election_id' => $electionId,
            'position_id' => $positionId,
            'candidate_id' => $candidateId,
            'tally_hash' => hash('sha256', $electionId . $positionId . $candidateId . now()->timestamp),
        ]);
    }

    public function getWinnerForPosition(int $electionId, int $positionId): ?int
    {
        $result = $this->model->where('election_id', $electionId)
            ->where('position_id', $positionId)
            ->selectRaw('candidate_id, COUNT(*) as vote_count')
            ->groupBy('candidate_id')
            ->orderByDesc('vote_count')
            ->first();

        return $result ? $result->candidate_id : null;
    }

    public function getElectionResults(int $electionId): array
    {
        $tallies = $this->getTallyForElection($electionId);
        $results = [];

        foreach ($tallies->groupBy('position_id') as $positionId => $positionTallies) {
            $results[$positionId] = $positionTallies->map(function ($tally) {
                return [
                    'candidate_id' => $tally->candidate_id,
                    'vote_count' => $tally->vote_count,
                ];
            })->sortByDesc('vote_count')->values();
        }

        return $results;
    }

    public function getTotalVotesForElection(int $electionId): int
    {
        return $this->model->where('election_id', $electionId)->count();
    }

    public function getTotalVotesForCandidate(int $electionId, int $candidateId): int
    {
        return $this->model->where('election_id', $electionId)
            ->where('candidate_id', $candidateId)
            ->count();
    }
}