<?php

namespace App\Services\Audit;

use App\Models\Audit\AuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\Audit\AuditEventType;

class AuditDisplayService
{
    public function getRecentActivity(int $limit = 10, string $severityFilter = 'all'): Collection
    {
        $cacheKey = 'observer_recent_activity_' . $severityFilter . '_' . $limit;
        return Cache::remember($cacheKey, 300, function () use ($limit, $severityFilter) {
            // Single query with polymorphic relationships
            $logs = AuditLog::with(['user', 'entity'])
                ->orderBy('created_at', 'desc')
                ->limit($limit * 3) // Get more to filter by severity
                ->get();

            // Eager load nested relationships based on entity type
            $this->eagerLoadNestedRelations($logs);

            $formatted = $logs->map(fn($log) => $this->formatAuditLog($log));
            
            // Filter by severity if specified
            if ($severityFilter !== 'all') {
                $formatted = $formatted->filter(fn($log) => $log['severity'] === $severityFilter);
            }
            
            return $formatted->take($limit);
        });
    }

    private function eagerLoadNestedRelations(Collection $logs): void
    {
        // Group entities by type for efficient loading
        $candidateIds = [];
        $electionIds = [];
        $userIds = [];

        foreach ($logs as $log) {
            if ($log->entity_type === 'App\\Models\\Candidate\\Candidate' && $log->entity) {
                $candidateIds[] = $log->entity->id;
            } elseif ($log->entity_type === 'App\\Models\\Election\\Election' && $log->entity) {
                $electionIds[] = $log->entity->id;
            } elseif ($log->entity_type === 'App\\Models\\User' && $log->entity) {
                $userIds[] = $log->entity->id;
            }
        }

        // Bulk load all nested relationships
        if (!empty($candidateIds)) {
            \App\Models\Candidate\Candidate::with(['user', 'election', 'position'])
                ->whereIn('id', $candidateIds)
                ->get()
                ->keyBy('id');
        }
    }

    private function formatAuditLog(AuditLog $log): array
    {
        $metadata = $this->extractMetadata($log);
        
        return [
            'id' => $log->id,
            'action' => $log->getActionLabel(),
            'description' => $this->generateDescription($log, $metadata),
            'user_name' => $log->getUserName(),
            'created_at' => $log->created_at,
            'entity_type' => $this->formatEntityType($log->entity_type),
            'metadata' => $metadata,
            'severity' => $this->getSeverity($log->action),
        ];
    }

    private function extractMetadata(AuditLog $log): array
    {
        if (!$log->entity) {
            return [];
        }

        return match($log->entity_type) {
            'App\\Models\\Candidate\\Candidate' => [
                'candidate_name' => $log->entity->user->full_name ?? 'Unknown',
                'election_title' => $log->entity->election->title ?? 'Unknown',
                'position_title' => $log->entity->position->title ?? 'Unknown',
            ],
            'App\\Models\\User' => [
                'user_name' => $log->entity->full_name ?? 'Unknown',
            ],
            'App\\Models\\Election\\Election' => [
                'election_title' => $log->entity->title ?? 'Unknown',
            ],
            default => []
        };
    }

    private function generateDescription(AuditLog $log, array $metadata): string
    {
        return match($log->action) {
            'candidate_approved' => "Candidate " . ($metadata['candidate_name'] ?? 'Unknown') . " approved for " . ($metadata['position_title'] ?? 'Unknown Position') . " in " . ($metadata['election_title'] ?? 'Unknown Election'),
            'candidate_rejected' => "Candidate " . ($metadata['candidate_name'] ?? 'Unknown') . " rejected for " . ($metadata['position_title'] ?? 'Unknown Position') . " in " . ($metadata['election_title'] ?? 'Unknown Election'),
            'user_approved' => "User " . ($metadata['user_name'] ?? 'Unknown') . " account approved",
            'election_created' => "Election '" . ($metadata['election_title'] ?? 'Unknown') . "' created",
            default => $log->getActionLabel()
        };
    }

    private function formatEntityType(?string $entityType): string
    {
        if (!$entityType) return 'System';
        
        $className = class_basename($entityType);
        return match($className) {
            'Candidate' => 'Candidate',
            'User' => 'User', 
            'Election' => 'Election',
            default => $className
        };
    }

    private function getSeverity(string $action): string
    {
        return match($action) {
            'candidate_rejected', 'user_rejected' => 'high',
            'candidate_approved', 'user_approved' => 'medium',
            default => 'low'
        };
    }

    public function getAuditLogs(
        ?string $search = null,
        ?string $eventType = null,
        ?string $severity = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = AuditLog::with(['user', 'entity'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($eventType) {
            $query->where('action', $eventType);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Apply severity filter by checking the action
        if ($severity) {
            $actionsForSeverity = $this->getActionsBySeverity($severity);
            if (!empty($actionsForSeverity)) {
                $query->whereIn('action', $actionsForSeverity);
            }
        }

        $logs = $query->paginate($perPage);
        
        // Transform the paginated results
        $logs->getCollection()->transform(function ($log) {
            $metadata = $this->extractMetadata($log);
            return (object) [
                'id' => $log->id,
                'event_type' => $log->getActionLabel(),
                'description' => $this->generateDescription($log, $metadata),
                'user_name' => $log->getUserName(),
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at,
                'entity_name' => $this->getEntityName($log),
                'metadata' => $metadata,
                'severity' => $this->getSeverity($log->action),
            ];
        });

        return $logs;
    }

    public function getEventTypes(): array
    {
        $actions = AuditLog::distinct('action')
            ->pluck('action')
            ->filter()
            ->toArray();
            
        // Convert raw actions to formatted labels
        $eventTypes = [];
        foreach ($actions as $action) {
            $log = new AuditLog(['action' => $action]);
            $eventTypes[$action] = $log->getActionLabel();
        }
        
        asort($eventTypes); // Sort by label
        return $eventTypes;
    }

    public function getSeverityLevels(): array
    {
        return ['low', 'medium', 'high', 'critical'];
    }

    private function getEntityName(AuditLog $log): ?string
    {
        if (!$log->entity) {
            return null;
        }

        return match($log->entity_type) {
            'App\\Models\\Candidate\\Candidate' => $log->entity->user->full_name ?? 'Unknown Candidate',
            'App\\Models\\User' => $log->entity->full_name ?? 'Unknown User',
            'App\\Models\\Election\\Election' => $log->entity->title ?? 'Unknown Election',
            default => class_basename($log->entity_type)
        };
    }

    private function getActionsBySeverity(string $severity): array
    {
        $allActions = AuditLog::distinct('action')->pluck('action')->toArray();
        
        return array_filter($allActions, function($action) use ($severity) {
            return $this->getSeverity($action) === $severity;
        });
    }
}