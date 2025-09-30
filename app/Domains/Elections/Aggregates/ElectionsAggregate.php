<?php

namespace App\Domains\Elections\Aggregates;

use App\Domains\Elections\Entities\Election;
use App\Domains\Elections\Entities\Candidate;
use App\Domains\Elections\ValueObjects\ElectionStatus;
use App\Domains\Elections\ValueObjects\PositionType;
use App\Domains\Elections\DomainEvents\ElectionCreatedEvent;
use App\Domains\Elections\DomainEvents\ElectionStartedEvent;
use App\Domains\Elections\DomainEvents\ElectionCompletedEvent;
use App\Domains\Elections\DomainEvents\CandidateApprovedEvent;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ElectionsAggregate
{
    private Collection $elections;
    private Collection $candidates;
    private int $maxActiveElections = 3;

    public function __construct()
    {
        $this->elections = collect();
        $this->candidates = collect();
    }

    public function createElection(
        string $title,
        string $description,
        Carbon $startDate,
        Carbon $endDate,
        int $createdBy
    ): Election {
        $this->ensureCanCreateElection();

        $election = new Election($title, $description, $startDate, $endDate, $createdBy);

        $this->elections->push($election);

        // Raise domain event
        event(new ElectionCreatedEvent($election));

        return $election;
    }

    public function announceElection(Election $election): void
    {
        $election->announce();
    }

    public function startElection(Election $election): void
    {
        $election->start();

        // Raise domain event
        event(new ElectionStartedEvent($election));
    }

    public function completeElection(Election $election): void
    {
        $election->complete();

        // Raise domain event
        event(new ElectionCompletedEvent($election));
    }

    public function cancelElection(Election $election): void
    {
        $election->cancel();
    }

    public function registerCandidate(
        int $electionId,
        int $userId,
        PositionType $position,
        string $manifesto
    ): Candidate {
        $this->ensureElectionExists($electionId);
        $this->ensureUserNotAlreadyCandidate($electionId, $userId, $position);

        $candidate = new Candidate($electionId, $userId, $position, $manifesto);

        $this->candidates->push($candidate);

        return $candidate;
    }

    public function approveCandidate(Candidate $candidate, int $approvedBy): void
    {
        $candidate->approve($approvedBy);

        // Raise domain event
        event(new CandidateApprovedEvent($candidate));
    }

    public function getActiveElections(): Collection
    {
        return $this->elections->filter(fn(Election $election) => $election->isActive());
    }

    public function getElectionsByStatus(ElectionStatus $status): Collection
    {
        return $this->elections->filter(fn(Election $election) =>
            $election->getStatus()->equals($status)
        );
    }

    public function getCandidatesForElection(int $electionId): Collection
    {
        return $this->candidates->filter(fn(Candidate $candidate) =>
            $candidate->getElectionId() === $electionId
        );
    }

    public function getApprovedCandidatesForElection(int $electionId): Collection
    {
        return $this->getCandidatesForElection($electionId)->filter(fn(Candidate $candidate) =>
            $candidate->isApproved()
        );
    }

    public function getCandidatesByPosition(int $electionId, PositionType $position): Collection
    {
        return $this->getCandidatesForElection($electionId)->filter(fn(Candidate $candidate) =>
            $candidate->getPosition()->equals($position)
        );
    }

    public function getElectionById(int $electionId): ?Election
    {
        return $this->elections->first(fn(Election $election) => $election->getId() === $electionId);
    }

    public function getCandidateById(int $candidateId): ?Candidate
    {
        return $this->candidates->first(fn(Candidate $candidate) => $candidate->getId() === $candidateId);
    }

    private function ensureCanCreateElection(): void
    {
        $activeCount = $this->getActiveElections()->count();
        if ($activeCount >= $this->maxActiveElections) {
            throw new \DomainException('Maximum number of active elections reached');
        }
    }

    private function ensureElectionExists(int $electionId): void
    {
        $election = $this->getElectionById($electionId);
        if (!$election) {
            throw new \DomainException('Election not found');
        }

        if (!$election->isActive()) {
            throw new \DomainException('Election is not active for candidate registration');
        }
    }

    private function ensureUserNotAlreadyCandidate(int $electionId, int $userId, PositionType $position): void
    {
        $existingCandidate = $this->candidates->first(function (Candidate $candidate) use ($electionId, $userId, $position) {
            return $candidate->getElectionId() === $electionId
                && $candidate->getUserId() === $userId
                && $candidate->getPosition()->equals($position);
        });

        if ($existingCandidate) {
            throw new \DomainException('User is already a candidate for this position in this election');
        }
    }
}