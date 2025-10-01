<?php

namespace App\Services\Application;

use App\Domains\Elections\Repository\ElectionsRepositoryInterface;
use Illuminate\Support\Collection;

class ElectionsApplicationService
{
    private ElectionsRepositoryInterface $electionsRepository;

    public function __construct(ElectionsRepositoryInterface $electionsRepository)
    {
        $this->electionsRepository = $electionsRepository;
    }

    public function getActiveElections(): Collection
    {
        return $this->electionsRepository->getActiveElections();
    }

    public function getElectionResults(int $electionId): array
    {
        return $this->electionsRepository->getElectionResults($electionId);
    }
}
