<?php

namespace App\Domains\Elections\DomainServices;

use App\Domains\Elections\Aggregates\ElectionsAggregate;
use App\Domains\Elections\ValueObjects\ElectionStatus;
use App\Domains\Elections\ValueObjects\PositionType;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ElectionsDomainService
{
    private ElectionsAggregate $aggregate;

    public function __construct(ElectionsAggregate $aggregate)
    {
        $this->aggregate = $aggregate;
    }

    public function scheduleElection(
        string $title,
        string $description,
        Carbon $startDate,
        Carbon $endDate,
        int $createdBy
    ): array {
        $election = $this->aggregate->createElection($title, $description, $startDate, $endDate, $createdBy);

        return [
            'election_id' => $election->getId(),
            'title' => $election->getTitle(),
            'status' => $election->getStatus()->getStatus(),
            'start_date' => $election->getStartDate()->toDateTimeString(),
            'end_date' => $election->getEndDate()->toDateTimeString()
        ];
    }

    public function announceElection(int $electionId): void
    {
        $election = $this->aggregate->getElectionById($electionId);
        if (!$election) {
            throw new \DomainException('Election not found');
        }

        $this->aggregate->announceElection($election);
    }

    public function startElection(int $electionId): void
    {
        $election = $this->aggregate->getElectionById($electionId);
        if (!$election) {
            throw new \DomainException('Election not found');
        }

        $this->aggregate->startElection($election);
    }

    public function completeElection(int $electionId): void
    {
        $election = $this->aggregate->getElectionById($electionId);
        if (!$election) {
            throw new \DomainException('Election not found');
        }

        $this->aggregate->completeElection($election);
    }

    public function registerCandidate(
        int $electionId,
        int $userId,
        string $position,
        string $manifesto
    ): array {
        $positionType = new PositionType($position);
        $candidate = $this->aggregate->registerCandidate($electionId, $userId, $positionType, $manifesto);

        return [
            'candidate_id' => $candidate->getId(),
            'election_id' => $candidate->getElectionId(),
            'position' => $candidate->getPosition()->getType(),
            'is_approved' => $candidate->isApproved()
        ];
    }

    public function approveCandidate(int $candidateId, int $approvedBy): void
    {
        $candidate = $this->aggregate->getCandidateById($candidateId);
        if (!$candidate) {
            throw new \DomainException('Candidate not found');
        }

        $this->aggregate->approveCandidate($candidate, $approvedBy);
    }

    public function getElectionOverview(int $electionId): array
    {
        $election = $this->aggregate->getElectionById($electionId);
        if (!$election) {
            throw new \DomainException('Election not found');
        }

        $candidates = $this->aggregate->getCandidatesForElection($electionId);
        $approvedCandidates = $this->aggregate->getApprovedCandidatesForElection($electionId);

        return [
            'election' => [
                'id' => $election->getId(),
                'title' => $election->getTitle(),
                'status' => $election->getStatus()->getStatus(),
                'start_date' => $election->getStartDate()->toDateTimeString(),
                'end_date' => $election->getEndDate()->toDateTimeString(),
                'is_active' => $election->isActive()
            ],
            'candidates' => [
                'total' => $candidates->count(),
                'approved' => $approvedCandidates->count(),
                'pending' => $candidates->filter(fn($c) => !$c->isApproved())->count()
            ],
            'positions' => $this->getPositionBreakdown($electionId)
        ];
    }

    public function validateElectionSchedule(Carbon $startDate, Carbon $endDate): bool
    {
        $activeElections = $this->aggregate->getActiveElections();

        foreach ($activeElections as $election) {
            // Check for overlapping elections
            if ($startDate->between($election->getStartDate(), $election->getEndDate()) ||
                $endDate->between($election->getStartDate(), $election->getEndDate())) {
                return false;
            }
        }

        return true;
    }

    private function getPositionBreakdown(int $electionId): array
    {
        $breakdown = [];
        $positions = ['president', 'governor', 'senator', 'representative', 'chairman', 'councilor'];

        foreach ($positions as $position) {
            $candidates = $this->aggregate->getCandidatesByPosition($electionId, new PositionType($position));
            $approved = $candidates->filter(fn($c) => $c->isApproved())->count();

            if ($candidates->count() > 0) {
                $breakdown[$position] = [
                    'total_candidates' => $candidates->count(),
                    'approved_candidates' => $approved
                ];
            }
        }

        return $breakdown;
    }
}