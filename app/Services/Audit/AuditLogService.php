<?php

namespace App\Services\Audit;

use App\Models\Audit\AuditLog;
use App\Models\User;
use App\Models\Admin;
use App\Services\Cryptographic\CryptographicService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;
use InvalidArgumentException;

class AuditLogService
{
    public function __construct(
        private CryptographicService $crypto
    ) {}

    /**
     * Log an action to the audit trail
     */
    public function log(
        string $action,
        User|Admin|null $user = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        // Use authenticated user if no user provided
        $user = $user ?? Auth::user() ?? Auth::guard('admin')->user();

        // Get the last audit log for chain linking
        $lastLog = AuditLog::orderBy('created_at', 'desc')->first();
        $previousHash = $lastLog?->integrity_hash;

        // Handle both User and Admin models
        $userId = null;
        $userType = null;
        if ($user instanceof Admin) {
            $userId = $user->id;
            $userType = 'admin';
        } elseif ($user instanceof User) {
            $userId = $user->id;
            $userType = 'user';
        }
        
        $logData = [
            'user_id' => $userId,
            'user_type' => $userType,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()->toISOString(),
        ];

        $integrityHash = $this->crypto->generateAuditHash($logData, $previousHash);

        return AuditLog::create([
            'uuid' => Str::uuid(),
            'user_id' => $userId,
            'user_type' => $userType,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'integrity_hash' => $integrityHash,
            'previous_hash' => $previousHash,
        ]);
    }

    /**
     * Log user action with automatic context
     */
    public function logUserAction(string $action, $entity = null, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        $entityType = null;
        $entityId = null;

        if ($entity) {
            $entityType = get_class($entity);
            $entityId = $entity->id ?? null;
        }

        return $this->log($action, Auth::user(), $entityType, $entityId, $oldValues, $newValues);
    }

    /**
     * Log system action
     */
    public function logSystemAction(string $action, $entity = null, ?array $data = null): AuditLog
    {
        $entityType = null;
        $entityId = null;

        if ($entity) {
            $entityType = get_class($entity);
            $entityId = $entity->id ?? null;
        }

        return $this->log($action, null, $entityType, $entityId, null, $data);
    }

    /**
     * Log system action with IP hash handling for console commands
     */
    public function logConsoleAction(string $action, $entity = null, ?array $data = null): AuditLog
    {
        $entityType = null;
        $entityId = null;

        if ($entity) {
            $entityType = get_class($entity);
            $entityId = $entity->id ?? null;
        }

        // For console commands, use a special IP hash
        $consoleIpHash = hash('sha256', 'console-command-' . gethostname());

        $logData = [
            'user_id' => null,
            'user_type' => 'system',
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => null,
            'new_values' => $data,
            'ip_address' => $consoleIpHash,
            'user_agent' => 'Console Command',
            'created_at' => now()->toISOString(),
        ];

        $previousHash = AuditLog::orderBy('created_at', 'desc')->first()?->integrity_hash;
        $integrityHash = $this->crypto->generateAuditHash($logData, $previousHash);

        return AuditLog::create([
            'uuid' => Str::uuid(),
            'user_id' => null,
            'user_type' => 'system',
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => null,
            'new_values' => $data,
            'ip_address' => $consoleIpHash,
            'user_agent' => 'Console Command',
            'integrity_hash' => $integrityHash,
            'previous_hash' => $previousHash,
        ]);
    }

    /**
     * Verify audit log integrity
     */
    public function verifyIntegrity(?int $logId = null): bool
    {
        if ($logId) {
            $log = AuditLog::findOrFail($logId);
            return $log->verifyIntegrity();
        }

        return AuditLog::verifyChainIntegrity();
    }

    /**
     * Get audit logs for a specific entity
     */
    public function getEntityAuditTrail(string $entityType, int $entityId): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::forEntity($entityType, $entityId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit logs for a specific user
     */
    public function getUserAuditTrail(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::forUser($userId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent audit logs
     */
    public function getRecentLogs(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search audit logs
     */
    public function searchLogs(array $filters): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = AuditLog::with('user');

        if (!empty($filters['action'])) {
            $query->where('action', 'LIKE', '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['action']) . '%');
        }

        if (!empty($filters['user_id']) && is_numeric($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (!empty($filters['entity_type']) && is_string($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['ip_address'])) {
            $hashedIp = $this->crypto->hashIpAddress($filters['ip_address']);
            $query->where('ip_address', $hashedIp);
        }

        return $query->orderBy('created_at', 'desc')->paginate(50);
    }

    /**
     * Export audit logs
     */
    public function exportLogs(array $filters = [], string $format = 'csv'): string
    {
        $query = AuditLog::with('user');

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['action']) && is_string($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'audit_logs_' . now()->format('Y_m_d_H_i_s') . '.' . $format;
        $filepath = storage_path('app/exports/' . $filename);

        switch ($format) {
            case 'csv':
                $this->exportToCsv($logs, $filepath);
                break;
            case 'json':
                $this->exportToJson($logs, $filepath);
                break;
            default:
                throw new InvalidArgumentException("Unsupported export format: {$format}");
        }

        return $filepath;
    }

    /**
     * Export logs to CSV
     */
    private function exportToCsv(\Illuminate\Database\Eloquent\Collection $logs, string $filepath): void
    {
        $file = fopen($filepath, 'w');
        
        if (!$file) {
            throw new RuntimeException("Cannot create export file: {$filepath}");
        }
        
        try {
            // Write headers
            fputcsv($file, [
                'ID',
                'UUID',
                'User',
                'Action',
                'Entity Type',
                'Entity ID',
                'IP Address',
                'User Agent',
                'Created At',
                'Integrity Hash',
            ]);

            // Write data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->uuid,
                    $log->getUserName(),
                    $log->getActionLabel(),
                    $log->entity_type,
                    $log->entity_id,
                    $log->ip_address,
                    $log->user_agent,
                    $log->created_at->toISOString(),
                    $log->integrity_hash,
                ]);
            }
        } finally {
            fclose($file);
        }
    }

    /**
     * Export logs to JSON
     */
    private function exportToJson(\Illuminate\Database\Eloquent\Collection $logs, string $filepath): void
    {
        $data = [
            'export_timestamp' => now()->toISOString(),
            'total_records' => $logs->count(),
            'integrity_verified' => $this->verifyIntegrity(),
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'uuid' => $log->uuid,
                    'user_id' => $log->user_id,
                    'user_name' => $log->getUserName(),
                    'action' => $log->action,
                    'action_label' => $log->getActionLabel(),
                    'entity_type' => $log->entity_type,
                    'entity_id' => $log->entity_id,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'created_at' => $log->created_at->toISOString(),
                    'integrity_hash' => $log->integrity_hash,
                    'previous_hash' => $log->previous_hash,
                ];
            })->toArray(),
        ];

        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Get audit statistics
     */
    public function getStatistics(): array
    {
        return AuditLog::getStatistics();
    }

    /**
     * Clean old audit logs (if retention policy is set)
     */
    public function cleanOldLogs(): int
    {
        $retentionDays = config('ayapoll.audit.retention_days');
        
        if (!$retentionDays) {
            return 0; // No retention policy set
        }

        return AuditLog::where('created_at', '<', now()->subDays($retentionDays))->delete();
    }

    /**
     * Log user approval action
     */
    public function logUserApproval(User $user, Admin $admin): AuditLog
    {
        return $this->log(
            'user_approved',
            $admin,
            User::class,
            $user->id,
            ['status' => $user->getOriginal('status')],
            ['status' => $user->status->value, 'approved_at' => $user->approved_at, 'approved_by' => $user->approved_by]
        );
    }

    /**
     * Log user rejection action
     */
    public function logUserRejection(User $user, Admin $admin, string $reason): AuditLog
    {
        return $this->log(
            'user_rejected',
            $admin,
            User::class,
            $user->id,
            ['status' => $user->getOriginal('status')],
            ['status' => $user->status->value, 'rejection_reason' => $reason, 'rejected_at' => now(), 'rejected_by' => $admin->id]
        );
    }

    /**
     * Get integrity report
     */
    public function getIntegrityReport(): array
    {
        $totalLogs = AuditLog::count();
        $verifiedLogs = 0;
        $brokenChainLogs = [];

        $logs = AuditLog::orderBy('created_at')->get();

        foreach ($logs as $log) {
            if ($log->verifyIntegrity()) {
                $verifiedLogs++;
            } else {
                $brokenChainLogs[] = [
                    'id' => $log->id,
                    'action' => $log->action,
                    'created_at' => $log->created_at,
                    'integrity_hash' => $log->integrity_hash,
                ];
            }
        }

        return [
            'total_logs' => $totalLogs,
            'verified_logs' => $verifiedLogs,
            'integrity_percentage' => $totalLogs > 0 ?
                round(($verifiedLogs / $totalLogs) * 100, 2) : 100,
            'chain_integrity' => AuditLog::verifyChainIntegrity(),
            'broken_chain_logs' => $brokenChainLogs,
            'last_verified_at' => now(),
        ];
    }
}