<?php

namespace App\Services\Candidate;

use App\Models\Candidate\Candidate;
use App\Models\Admin;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;

class CandidatePaymentService
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function processPayment(Candidate $candidate, string $paymentReference): bool
    {
        if (!$candidate->payment_status->canProcessPayment()) {
            throw new \InvalidArgumentException('Payment cannot be processed for this candidate');
        }

        return DB::transaction(function () use ($candidate, $paymentReference) {
            $success = $candidate->update([
                'payment_status' => 'paid',
                'payment_reference' => $paymentReference,
            ]);

            if ($success) {
                $this->auditLog->log(
                    'candidate_payment_processed',
                    $candidate->user,
                    Candidate::class,
                    $candidate->id,
                    ['payment_status' => 'pending'],
                    ['payment_status' => 'paid', 'reference' => $paymentReference]
                );
            }

            return $success;
        });
    }

    public function waivePayment(Candidate $candidate, Admin $admin, string $reason): bool
    {
        if (!$candidate->payment_status->canBeWaived()) {
            throw new \InvalidArgumentException('Payment cannot be waived for this candidate');
        }

        return DB::transaction(function () use ($candidate, $admin, $reason) {
            $success = $candidate->update([
                'payment_status' => 'waived',
                'payment_reference' => 'WAIVED_BY_ADMIN',
            ]);

            if ($success) {
                $this->auditLog->log(
                    'candidate_payment_waived',
                    $admin,
                    Candidate::class,
                    $candidate->id,
                    ['payment_status' => 'pending'],
                    ['payment_status' => 'waived', 'reason' => $reason]
                );
            }

            return $success;
        });
    }
}