<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessDataRetentionCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-retention:cleanup
                            {--dry-run : Show what would be processed without making changes}
                            {--policy-type= : Process only specific policy type (user_data, documents, logs)}
                            {--limit= : Limit number of records to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automated data retention cleanup based on configured policies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $policyType = $this->option('policy-type');
        $limit = $this->option('limit');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🧹 Starting data retention cleanup process...');

        $dataRetentionService = app(\App\Services\DataRetentionService::class);

        // Get retention statistics
        $stats = $dataRetentionService->getRetentionStats();
        $this->displayStats($stats);

        // Process cleanup
        if (!$dryRun) {
            $results = $dataRetentionService->processAutomatedCleanup();

            $this->info('✅ Cleanup completed:');
            $this->line('   Processed users: ' . $results['processed_users']);
            $this->line('   Deleted users: ' . $results['deleted_users']);
            $this->line('   Anonymized users: ' . $results['anonymized_users']);

            if (!empty($results['errors'])) {
                $this->error('❌ Errors encountered:');
                foreach ($results['errors'] as $error) {
                    $this->error('   - ' . ($error['user_id'] ?? 'General') . ': ' . $error['error']);
                }
            }
        } else {
            // Dry run - just show what would be processed
            $expiredUsers = \App\Models\User::whereNotNull('data_retention_until')
                ->where('data_retention_until', '<', now())
                ->when($limit, fn($q) => $q->limit($limit))
                ->count();

            $this->info('📊 Dry run results:');
            $this->line('   Users that would be processed: ' . $expiredUsers);
        }

        $this->info('🎉 Data retention cleanup process completed!');
    }

    /**
     * Display retention statistics
     */
    private function displayStats(array $stats): void
    {
        $this->info('📈 Current Data Retention Statistics:');
        $this->line('   Total users: ' . number_format($stats['total_users']));
        $this->line('   Users with retention policy: ' . number_format($stats['users_with_retention_policy']));
        $this->line('   Policy coverage: ' . $stats['policy_coverage'] . '%');
        $this->line('   Expired users: ' . number_format($stats['expired_users']));
        $this->line('   Pending deletion: ' . number_format($stats['pending_deletion']));
        $this->line('   Active policies: ' . $stats['policies']);
        $this->newLine();
    }
}
