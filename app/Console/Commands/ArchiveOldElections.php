<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Election\ElectionArchiveService;
use Illuminate\Support\Facades\Log;
use Exception;

class ArchiveOldElections extends Command
{
    protected $signature = 'elections:archive-old {--dry-run : Show what would be archived without actually archiving}';
    protected $description = 'Archive elections that have been completed for more than 30 days';

    public function handle(ElectionArchiveService $archiveService): int
    {
        try {
            $elections = $archiveService->getArchivableElections();
        } catch (Exception $e) {
            $this->error("Failed to retrieve archivable elections: {$e->getMessage()}");
            return self::FAILURE;
        }
        
        if ($elections->isEmpty()) {
            $this->info('No elections found for archiving.');
            return self::SUCCESS;
        }

        $archived = 0;
        $failed = 0;
        
        $isDryRun = $this->option('dry-run');
        
        foreach ($elections as $election) {
            try {
                if ($isDryRun) {
                    $this->info("[DRY RUN] Would archive election: {$election->title}");
                    $archived++;
                } else {
                    if ($archiveService->archiveElection($election)) {
                        $this->info("Archived election: {$election->title}");
                        Log::info('Election archived via command', [
                            'election_id' => $election->id,
                            'election_title' => $election->title
                        ]);
                        $archived++;
                    } else {
                        $this->error("Failed to archive election: {$election->title}");
                        Log::error('Failed to archive election via command', [
                            'election_id' => $election->id,
                            'election_title' => $election->title
                        ]);
                        $failed++;
                    }
                }
            } catch (Exception $e) {
                $this->error("Exception archiving election {$election->title}: {$e->getMessage()}");
                Log::error('Exception during election archiving', [
                    'election_id' => $election->id ?? 'unknown',
                    'election_title' => $election->title ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $failed++;
            }
        }

        $message = $isDryRun ? "Would archive {$archived} elections" : "Successfully archived {$archived} elections";
        $this->info($message);
        
        Log::info('Archive elections command completed', [
            'archived_count' => $archived,
            'failed_count' => $failed,
            'dry_run' => $isDryRun
        ]);
        
        if ($failed > 0) {
            $this->error("Failed to archive {$failed} elections.");
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}