<?php

namespace App\Services\Observer;

use App\Models\Observer;
use App\Models\Admin;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;

class ObserverService
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function updateObserver(Observer $observer, Admin $admin, array $data): bool
    {
        if (!$admin->hasPermission('manage-observers')) {
            throw new \InvalidArgumentException('Insufficient permissions');
        }

        return DB::transaction(function () use ($observer, $admin, $data) {
            $oldData = $observer->only(['access_level', 'status']);
            
            $success = $observer->update($data);

            if ($success) {
                $this->auditLog->log(
                    'observer_updated',
                    $admin,
                    Observer::class,
                    $observer->id,
                    $oldData,
                    $data
                );
            }

            return $success;
        });
    }
}