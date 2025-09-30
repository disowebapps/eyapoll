<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Enums\Election\ElectionPhase;
use App\Services\Election\VoterRegistrationService;
use App\Services\Audit\AuditService;
use App\Models\Admin;

class ElectionPhaseManager
{
    public function __construct(
        private VoterRegistrationService $registrationService,
        private AuditService $auditService
    ) {}

    public function transitionToPhase(Election $election, ElectionPhase $targetPhase, Admin $admin): bool
    {
        $currentPhase = $election->phase ?? ElectionPhase::SETUP;
        
        // Security: Verify transition authorization
        if (!$this->canAdminTransition($admin, $election, $targetPhase)) {
            \Log::warning('Unauthorized phase transition attempt', [
                'admin_id' => $admin->id,
                'election_id' => $election->id,
                'target_phase' => $targetPhase->value
            ]);
            return false;
        }
        
        if (!$currentPhase->canTransitionTo($targetPhase)) {
            return false;
        }

        // Pre-transition validation
        if (!$this->validatePhaseRequirements($election, $targetPhase)) {
            return false;
        }

        // Execute phase-specific actions
        match($targetPhase) {
            ElectionPhase::CANDIDATE_REGISTRATION => $this->handleCandidateRegistrationPhase($election),
            ElectionPhase::CANDIDATE_REGISTRATION_CLOSED => $this->handleCandidateRegistrationClosedPhase($election),
            ElectionPhase::VOTER_REGISTRATION => $this->handleVoterRegistrationPhase($election),
            ElectionPhase::VERIFICATION => $this->handleVerificationPhase($election),
            ElectionPhase::VOTING => $this->handleVotingPhase($election),
            ElectionPhase::COLLATION => $this->handleCollationPhase($election),
            ElectionPhase::RESULTS_PUBLISHED => $this->handleResultsPhase($election),
            ElectionPhase::POST_ELECTION => $this->handlePostElectionPhase($election),
            default => null,
        };

        $election->update(['phase' => $targetPhase]);
        
        // Log the transition
        \Log::info('Election phase transition', [
            'election_id' => $election->id,
            'from_phase' => $currentPhase?->value,
            'to_phase' => $targetPhase->value,
            'admin_id' => $admin->id
        ]);
        
        return true;
    }

    private function handleCandidateRegistrationPhase(Election $election): void
    {
        \Log::info('Candidate application opened', ['election_id' => $election->id]);
    }

    private function handleCandidateRegistrationClosedPhase(Election $election): void
    {
        $election->update(['candidate_registration_closed' => true]);
        \Log::info('Candidate application closed', ['election_id' => $election->id]);
    }

    private function handleVoterRegistrationPhase(Election $election): void
    {
        \Log::info('Voter registration opened', ['election_id' => $election->id]);
    }

    private function handleVerificationPhase(Election $election): void
    {
        // CRITICAL: Verify all candidates have required documents
        $unverifiedCandidates = $election->candidates()
            ->whereHas('documents', fn($q) => $q->where('status', '!=', 'approved'))
            ->count();
            
        if ($unverifiedCandidates > 0) {
            throw new \Exception("Cannot enter verification phase: {$unverifiedCandidates} candidates have unverified documents");
        }

        // CRITICAL: Ensure minimum candidate threshold
        $approvedCandidates = $election->approvedCandidates()->count();
        if ($approvedCandidates < 2) {
            throw new \Exception("Cannot enter verification phase: Minimum 2 candidates required, found {$approvedCandidates}");
        }

        // Generate vote tokens (finalize voter registration)
        $registrationResult = $this->registrationService->generateVoterRegister($election);
        
        // Close candidate registration
        $election->update([
            'candidate_registration_closed' => true,
            'voter_register_locked' => true
        ]);
        
        \Log::info('Election verification phase completed', [
            'election_id' => $election->id,
            'approved_candidates' => $approvedCandidates,
            'eligible_voters' => $registrationResult['tokens_created'],
            'verification_timestamp' => now()->toISOString()
        ]);
    }

    private function handleVotingPhase(Election $election): void
    {
        // Lock voter register - no more changes allowed
        $election->update(['voter_register_locked' => true]);
        
        \Log::info('Election voting phase started', [
            'election_id' => $election->id,
            'eligible_voters' => $election->voteTokens()->count()
        ]);
    }

    private function handleCollationPhase(Election $election): void
    {
        // Stop accepting votes
        $election->update(['voting_closed' => true]);
        
        // Trigger result compilation
        app(\App\Services\Election\ResultCollationService::class)->compileResults($election);
        
        \Log::info('Election collation phase started', [
            'election_id' => $election->id,
            'total_votes' => $election->voteRecords()->count()
        ]);
    }

    private function handleResultsPhase(Election $election): void
    {
        $election->update([
            'results_published' => true,
            'voter_register_locked' => false // Resume voter registration for next election
        ]);
        
        \Log::info('Election results published - voter registration resumed', [
            'election_id' => $election->id
        ]);
    }

    private function handlePostElectionPhase(Election $election): void
    {
        // Archive election data, generate final reports
        \Log::info('Election entered post-election phase', [
            'election_id' => $election->id
        ]);
    }

    /**
     * SECURITY: Validate admin authorization for phase transitions
     */
    private function canAdminTransition(Admin $admin, Election $election, ElectionPhase $targetPhase): bool
    {
        // Super admins can transition any phase
        if ($admin->is_super_admin) {
            return true;
        }

        // Critical phases require super admin
        $criticalPhases = [ElectionPhase::VOTING, ElectionPhase::COLLATION, ElectionPhase::RESULTS_PUBLISHED];
        if (in_array($targetPhase, $criticalPhases)) {
            return false;
        }

        // Election creator can manage setup phases
        return $election->created_by === $admin->id;
    }

    /**
     * VALIDATION: Ensure phase requirements are met before transition
     */
    private function validatePhaseRequirements(Election $election, ElectionPhase $targetPhase): bool
    {
        return match($targetPhase) {
            ElectionPhase::CANDIDATE_REGISTRATION => $this->validateSetupComplete($election),
            ElectionPhase::VOTER_REGISTRATION => true, // Can run concurrently
            ElectionPhase::VERIFICATION => $this->validateRegistrationComplete($election),
            ElectionPhase::VOTING => $this->validateVerificationComplete($election),
            ElectionPhase::COLLATION => $this->validateVotingComplete($election),
            ElectionPhase::RESULTS_PUBLISHED => $this->validateCollationComplete($election),
            ElectionPhase::POST_ELECTION => $this->validateResultsPublished($election),
            default => true,
        };
    }

    private function validateSetupComplete(Election $election): bool
    {
        return !empty($election->title) && 
               !empty($election->description) && 
               $election->positions()->exists();
    }

    private function validateRegistrationComplete(Election $election): bool
    {
        return $election->candidates()->where('status', 'approved')->count() >= 2;
    }

    private function validateVerificationComplete(Election $election): bool
    {
        return $election->voter_register_locked && 
               $election->voteTokens()->count() > 0;
    }

    private function validateVotingComplete(Election $election): bool
    {
        return $election->ends_at <= now() || $election->voting_closed;
    }

    private function validateCollationComplete(Election $election): bool
    {
        return $election->voteTallies()->exists();
    }

    private function validateResultsPublished(Election $election): bool
    {
        return $election->results_published;
    }
}