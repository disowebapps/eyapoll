<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Election\Election;
use App\Models\Election\ElectionSnapshot;
use App\Services\Election\ElectionTimeService;
use App\Services\Audit\AuditLogService;

class SecureElectionMonitor extends Command
{
    protected $signature = 'elections:monitor';
    protected $description = 'Monitor elections with cryptographic integrity';
    
    public function __construct()
    {
        parent::__construct();
        // Command signature is not a credential - it's a CLI command name
    }

    public function handle()
    {
        $timeService = app(ElectionTimeService::class);
        $auditService = app(AuditLogService::class);
        
        Election::chunk(100, function ($elections) use ($timeService, $auditService) {
            foreach ($elections as $election) {
                try {
                    $currentStatus = $timeService->getElectionStatus($election);

                    if ($election->status !== $currentStatus) {
                        ElectionSnapshot::createSnapshot($election);

                        $originalStatus = $election->status;
                        $election->update(['status' => $currentStatus]);

                        $auditService->logConsoleAction(
                            "election_status_changed",
                            $election,
                            [
                                'from' => $originalStatus->value,
                                'to' => $currentStatus->value,
                                'trusted_time' => $timeService->getCurrentTime()
                            ]
                        );
                    }
                } catch (\App\Exceptions\InvalidElectionDatesException $e) {
                    // Skip elections with invalid dates
                    continue;
                }
            }
        });
        
        return 0;
    }
}