<?php

namespace App\Exceptions;

use App\Models\Election\Election;

class InvalidElectionDatesException extends ElectionException
{
    protected ?Election $election;

    public function __construct(string $message = "Election has invalid or missing dates", ?Election $election = null, array $context = [], int $code = 0, ?\Exception $previous = null)
    {
        $this->election = $election;
        $context['election_id'] = $election ? $election->id : null;
        parent::__construct($message, $context, $code, $previous);
    }

    public function getElection(): ?Election
    {
        return $this->election;
    }
}