<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Events\ElectionResultsPublished;
use App\Enums\Election\ElectionPhase;
use App\Enums\Election\ElectionStatus;
use App\Services\Election\ElectionArchiveService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class ElectionLifecycleService
{
    public function __construct(
        private ?ElectionArchiveService $archiveService = null
    ) {
        $this->archiveService = $archiveService ?? app(ElectionArchiveService::class);
    }

    public function transitionToOngoing(Election $election): bool
    {
        if (!$election->exists || !$election->status) {
            Log::warning('Invalid election for transition', ['election_id' => $election->id ?? 'null']);
            return false;
        }
        
        if (!$election->status->canBeStarted()) {
            return false;
        }

        try {
            return DB::transaction(function () use ($election) {
                $election->update(['status' => ElectionStatus::ONGOING]);
                
                Log::info('Election transitioned to ongoing', [
                    'election_id' => $election->id,
                    'started_at' => now(),
                ]);
                
                return true;
            });
        } catch (Exception $e) {
            Log::error('Failed to transition election to ongoing', [
                'election_id' => $election->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function transitionToCompleted(Election $election): bool
    {
        if (!$election->exists || !$election->status) {
            Log::warning('Invalid election for transition', ['election_id' => $election->id ?? 'null']);
            return false;
        }
        
        if (!$election->status->canBeEnded()) {
            return false;
        }

        try {
            return DB::transaction(function () use ($election) {
                $election->update([
                    'status' => ElectionStatus::COMPLETED,
                    'voting_closed' => true,
                ]);
                
                Log::info('Election transitioned to completed', [
                    'election_id' => $election->id,
                    'completed_at' => now(),
                ]);
                
                return true;
            });
        } catch (Exception $e) {
            Log::error('Failed to transition election to completed', [
                'election_id' => $election->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function archiveElection(Election $election): bool
    {
        if (!$election->exists || !$election->status) {
            Log::warning('Invalid election for archiving', ['election_id' => $election->id ?? 'null']);
            return false;
        }
        
        try {
            return $this->archiveService->archiveElection($election);
        } catch (Exception $e) {
            Log::error('Failed to archive election', [
                'election_id' => $election->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function publishResults(Election $election): bool
    {
        if (!$election->exists || !$election->phase) {
            Log::warning('Invalid election for results publishing', ['election_id' => $election->id ?? 'null']);
            return false;
        }
        
        if ($election->phase !== ElectionPhase::COLLATION) {
            return false;
        }

        try {
            return DB::transaction(function () use ($election) {
                $election->update([
                    'phase' => ElectionPhase::RESULTS_PUBLISHED,
                    'results_published' => true,
                    'results_published_at' => now()
                ]);

                ElectionResultsPublished::dispatch($election);
                return true;
            });
        } catch (Exception $e) {
            Log::error('Failed to publish election results', [
                'election_id' => $election->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getLifecycleStatus(Election $election): ElectionStatus
    {
        // Return current status if it's final or if no time-based logic needed
        if (in_array($election->status, [ElectionStatus::ARCHIVED, ElectionStatus::CANCELLED])) {
            return $election->status;
        }
        
        // Cache current time to avoid multiple now() calls
        static $now;
        $now = $now ?? now();
        
        // Time-based status determination
        if ($election->ends_at && $election->ends_at < $now) {
            return ElectionStatus::COMPLETED;
        }
        
        if ($election->starts_at && $election->ends_at && 
            $election->starts_at <= $now && $election->ends_at > $now) {
            return ElectionStatus::ONGOING;
        }
        
        return ElectionStatus::UPCOMING;
    }
}
