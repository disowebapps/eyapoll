<?php

namespace App\Domains\Elections\Repository;

use App\Domains\Elections\Entities\Election;
use App\Domains\Elections\Entities\Candidate;
use App\Domains\Elections\ValueObjects\ElectionStatus;
use App\Domains\Elections\ValueObjects\PositionType;
use Illuminate\Support\Collection;

interface ElectionsRepositoryInterface
{
    public function saveElection(Election $election): void;
    public function findElectionById(int $id): ?Election;
    public function getActiveElections(): Collection;
    public function getElectionsByStatus(ElectionStatus $status): Collection;
    public function updateElectionStatus(Election $election): void;

    public function saveCandidate(Candidate $candidate): void;
    public function findCandidateById(int $id): ?Candidate;
    public function getCandidatesForElection(int $electionId): Collection;
    public function getApprovedCandidatesForElection(int $electionId): Collection;
    public function getCandidatesByPosition(int $electionId, PositionType $position): Collection;
    public function updateCandidateStatus(Candidate $candidate): void;

    public function getElectionStatistics(): array;
    public function getCandidateStatistics(int $electionId): array;
}