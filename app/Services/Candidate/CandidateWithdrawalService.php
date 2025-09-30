<?php

namespace App\Services\Candidate;

use App\Models\Candidate\Candidate;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;

class CandidateWithdrawalService
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function withdrawApplication(Candidate $candidate, string $reason): bool
    {
        if (!$candidate->canWithdraw()) {
            throw new \InvalidArgumentException('Cannot withdraw this application');
        }

        return DB::transaction(function () use ($candidate, $reason) {
            $oldStatus = $candidate->status;
            
            $success = $candidate->update([
                'status' => 'withdrawn',
                'rejection_reason' => $reason,
            ]);

            if ($success) {
                $this->auditLog->log(
                    'candidate_application_withdrawn',
                    $candidate->user,
                    Candidate::class,
                    $candidate->id,
                    ['status' => $oldStatus->value],
                    ['status' => 'withdrawn', 'reason' => $reason]
                );
            }

            return $success;
        });
    }
}