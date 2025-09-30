<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Election\ElectionArchiveService;
use App\Jobs\Elections\ArchiveElection;
use Illuminate\Support\Facades\Log;
use Exception;

class AutoArchiveElections extends Command
{
    protected $signature = 'elections:archive-eligible
                            {--dry-run : Show what would be archived without actually archiving}
                            {--force : Override safety checks and archive anyway}
                            {--detailed : Show detailed output of the archiving process}
                            {--queue : Queue archiving jobs instead of processing immediately}';

    protected $description = 'Automatically identify and archive eligible elections based on predefined criteria';

    public function handle(ElectionArchiveService $archiveService): int
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');
        $detailed = $this->option('detailed');
        $useQueue = $this->option('queue');

        try {
            $this->info($isDryRun ? 'DRY RUN: Identifying elections for archiving...' : 'Identifying elections for archiving...');

            $elections = $archiveService->getArchivableElections();

            if ($elections->isEmpty()) {
                $this->info('No elections found that meet archiving criteria.');
                return self::SUCCESS;
            }

            $this->info("Found {$elections->count()} election(s) eligible for archiving:");

            if ($detailed || $isDryRun) {
                $this->displayElectionTable($elections);
            }

            if ($isDryRun) {
                $this->info("DRY RUN: Would archive {$elections->count()} elections.");
                return self::SUCCESS;
            }

            // Confirm action unless forced
            if (!$force && !$this->confirmArchiving($elections->count())) {
                $this->info('Archiving cancelled by user.');
                return self::SUCCESS;
            }

            $archived = 0;
            $failed = 0;
            $queued = 0;

            $progressBar = $this->output->createProgressBar($elections->count());
            $progressBar->start();

            foreach ($elections as $election) {
                try {
                    if ($useQueue) {
                        // Queue the archiving job
                        ArchiveElection::dispatch($election, $force)
                            ->onQueue('election-archiving')
                            ->delay(now()->addSeconds($queued * 5)); // Stagger jobs by 5 seconds

                        $queued++;
                        if ($detailed) {
                            $this->info("Queued archiving for election: {$election->title}");
                        }
                    } else {
                        // Process immediately
                        if ($archiveService->archiveElection($election, $force)) {
                            $archived++;
                            if ($detailed) {
                                $this->info("Archived election: {$election->title}");
                            }
                            Log::info('Election archived via command', [
                                'election_id' => $election->id,
                                'election_title' => $election->title,
                                'command' => 'elections:archive-eligible',
                            ]);
                        } else {
                            $failed++;
                            $this->error("Failed to archive election: {$election->title}");
                            Log::error('Failed to archive election via command', [
                                'election_id' => $election->id,
                                'election_title' => $election->title,
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    $failed++;
                    $this->error("Exception archiving election {$election->title}: {$e->getMessage()}");
                    Log::error('Exception during election archiving', [
                        'election_id' => $election->id ?? 'unknown',
                        'election_title' => $election->title ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            // Display results
            if ($useQueue) {
                $this->info("Successfully queued {$queued} elections for archiving.");
            } else {
                $this->info("Successfully archived {$archived} elections.");
            }

            if ($failed > 0) {
                $this->error("Failed to process {$failed} elections.");
                return self::FAILURE;
            }

            Log::info('Auto archive elections command completed', [
                'total_found' => $elections->count(),
                'archived_count' => $archived,
                'queued_count' => $queued,
                'failed_count' => $failed,
                'dry_run' => $isDryRun,
                'force' => $force,
                'use_queue' => $useQueue,
            ]);

            return self::SUCCESS;

        } catch (Exception $e) {
            $this->error("Command failed: {$e->getMessage()}");
            Log::error('Auto archive elections command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }
    }

    private function displayElectionTable($elections): void
    {
        $table = $this->table(
            ['ID', 'Title', 'Status', 'End Date', 'Days Since End', 'Has Active Appeals'],
            $elections->map(function ($election) {
                $daysSinceEnd = $election->ends_at ? now()->diffInDays($election->ends_at) : 'N/A';
                $hasActiveAppeals = $election->appeals()
                    ->whereIn('status', [\App\Enums\Appeal\AppealStatus::SUBMITTED, \App\Enums\Appeal\AppealStatus::UNDER_REVIEW])
                    ->exists() ? 'Yes' : 'No';

                return [
                    $election->id,
                    $election->title,
                    $election->status->label(),
                    $election->ends_at?->format('Y-m-d'),
                    $daysSinceEnd,
                    $hasActiveAppeals,
                ];
            })
        );
        $table->render();
    }

    private function confirmArchiving(int $count): bool
    {
        return $this->confirm(
            "Are you sure you want to archive {$count} election(s)? This action cannot be easily undone.",
            false
        );
    }
}