<?php

namespace App\Services\Candidate;

use App\Models\Candidate\Candidate;
use App\Models\User;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Services\Cryptographic\CryptographicService;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CandidateApplicationService
{
    public function __construct(
        private CryptographicService $crypto,
        private AuditLogService $auditLog
    ) {}

    public function submitApplication(User $user, array $data): Candidate
    {
        return DB::transaction(function () use ($user, $data) {
            // Validate eligibility
            $this->validateEligibility($user, $data['election_id'], $data['position_id']);
            
            $candidate = Candidate::create([
                'user_id' => $user->id,
                'election_id' => $data['election_id'],
                'position_id' => $data['position_id'],
                'manifesto' => $data['manifesto'],
                'application_fee' => $data['application_fee'] ?? 0,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Role upgrade handled separately for security
            // User role upgraded only after admin approval

            $this->auditLog->log('candidate_application_submitted', $user, Candidate::class, $candidate->id);
            
            // Enhanced audit logging using proper service
            app(\App\Services\Audit\AuditLogService::class)->log(
                'candidate_application_service_submission',
                $user,
                \App\Models\Candidate\Candidate::class,
                $candidate->id,
                null,
                [
                    'election_id' => $data['election_id'],
                    'position_id' => $data['position_id'],
                    'manifesto_length' => strlen($data['manifesto']),
                    'application_fee' => $data['application_fee'] ?? 0,
                ]
            );
            
            return $candidate;
        });
    }

    private function validateEligibility(User $user, int $electionId, int $positionId): void
    {
        if (!$user->canVote()) {
            throw new \InvalidArgumentException('User not eligible to apply as candidate');
        }

        $election = Election::findOrFail($electionId);
        if (!$election->canAcceptCandidateApplications()) {
            throw new \InvalidArgumentException('Election not accepting applications');
        }

        if (Candidate::where('user_id', $user->id)->where('election_id', $electionId)->exists()) {
            throw new \InvalidArgumentException('User already applied for this election');
        }
    }
}