<?php

namespace App\Services\Voting;

use App\Models\Voting\VoteAuthorization;
use App\Services\Audit\AuditService;

class VoteActivityTracker
{
    public function __construct(
        private AuditService $auditService,
        private BallotDraftService $draftService
    ) {}

    public function trackActivity(VoteAuthorization $auth, string $action, array $data = []): array
    {
        // Update last activity
        $auth->updateActivity();

        // Auto-extend if needed
        $extended = false;
        if ($this->shouldAutoExtend($auth, $action)) {
            $extended = $auth->extendTimeout(30); // 30 minutes extension
            if ($extended) {
                $this->auditService->logAuthorizationExtension($auth, 'auto_extend', $action);
            }
        }

        // Save draft if selections made
        if ($action === 'selection_made' && isset($data['selections'])) {
            $this->draftService->saveDraft($auth->voter_hash, $auth->election_id, $data['selections']);
        }

        // Log activity
        $this->auditService->logVoteActivity($auth, $action, $data);

        return [
            'time_left' => $auth->timeLeft(),
            'extended' => $extended,
            'extension_count' => $auth->extension_count
        ];
    }

    private function shouldAutoExtend(VoteAuthorization $auth, string $action): bool
    {
        return $auth->timeLeft() < 1800 && // Less than 30 minutes left
               $auth->extension_count < 10 && // Up to 10 extensions
               in_array($action, ['selection_made', 'position_viewed', 'candidate_viewed']);
    }
}