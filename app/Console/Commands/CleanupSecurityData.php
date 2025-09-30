<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupSecurityData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:cleanup {--days=30 : Number of days to keep security data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old security data (login attempts, expired IP blocks)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        $this->info("Cleaning up security data older than {$days} days...");

        // Clean up old login attempts
        $loginAttemptsDeleted = \App\Models\Security\LoginAttempt::cleanupOldAttempts($days);
        $this->info("Deleted {$loginAttemptsDeleted} old login attempts");

        // Clean up expired IP blocks
        $ipBlocksDeleted = \App\Models\Security\IpBlock::cleanupExpiredBlocks();
        $this->info("Deleted {$ipBlocksDeleted} expired IP blocks");

        // Clean up old audit logs (if retention policy is set)
        $auditLogsDeleted = app(\App\Services\Audit\AuditLogService::class)->cleanOldLogs();
        if ($auditLogsDeleted > 0) {
            $this->info("Deleted {$auditLogsDeleted} old audit logs");
        }

        $this->info('Security data cleanup completed successfully!');
    }
}
