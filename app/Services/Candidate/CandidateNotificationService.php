<?php

namespace App\Services\Candidate;

use App\Models\Candidate\Candidate;
use App\Jobs\Candidate\SendCandidateNotificationJob;

class CandidateNotificationService
{
    public function notifyApplicationSubmitted(Candidate $candidate): void
    {
        SendCandidateNotificationJob::dispatch(
            $candidate->user_id,
            'candidate_application_submitted',
            [
                'candidate_name' => $candidate->getDisplayName(),
                'election_title' => $candidate->election->title,
                'position_title' => $candidate->position->title,
                'application_fee' => $candidate->application_fee,
                'requires_payment' => $candidate->requiresPayment(),
            ]
        );
    }

    public function notifyApplicationApproved(Candidate $candidate): void
    {
        SendCandidateNotificationJob::dispatch(
            $candidate->user_id,
            'candidate_approved',
            [
                'candidate_name' => $candidate->getDisplayName(),
                'election_title' => $candidate->election->title,
                'position_title' => $candidate->position->title,
                'approved_at' => $candidate->approved_at,
            ]
        );
    }

    public function notifyApplicationRejected(Candidate $candidate): void
    {
        SendCandidateNotificationJob::dispatch(
            $candidate->user_id,
            'candidate_rejected',
            [
                'candidate_name' => $candidate->getDisplayName(),
                'election_title' => $candidate->election->title,
                'position_title' => $candidate->position->title,
                'rejection_reason' => $candidate->rejection_reason,
            ]
        );
    }

    public function notifyPaymentRequired(Candidate $candidate): void
    {
        SendCandidateNotificationJob::dispatch(
            $candidate->user_id,
            'candidate_payment_required',
            [
                'candidate_name' => $candidate->getDisplayName(),
                'election_title' => $candidate->election->title,
                'application_fee' => $candidate->application_fee,
                'payment_deadline' => $candidate->election->starts_at->subDays(7),
            ]
        );
    }
}