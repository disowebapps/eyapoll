<?php

namespace App\Livewire\Observer;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use App\Models\Audit\AuditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogs extends Component
{
    use WithPagination;

    #[Url(except: '', history: true)]
    public $search = '';
    
    #[Url(except: '', history: true)]
    public $eventType = '';
    
    #[Url(except: '', history: true)]
    public $severity = '';
    
    #[Url(except: '', history: true)]
    public $dateFrom = '';
    
    #[Url(except: '', history: true)]
    public $dateTo = '';

    public $perPage = 10;
    public $isLoading = false;
    public $autoRefresh = true;
    public $expandedLogs = [];

    protected $listeners = [
        'refresh-logs' => 'refreshLogs',
        'clear-filters' => 'clearFilters'
    ];

    public function updatedSearch()
    {
        $this->resetPage();
        $this->clearCache();
    }

    public function updatedEventType()
    {
        $this->resetPage();
        $this->clearCache();
    }

    public function updatedSeverity()
    {
        $this->resetPage();
        $this->clearCache();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
        $this->clearCache();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
        $this->clearCache();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'eventType', 'severity', 'dateFrom', 'dateTo']);
        $this->resetPage();
        $this->clearCache();
        $this->dispatch('filters-cleared');
    }

    public function refreshLogs()
    {
        $this->clearCache();
        // Force clear all related caches
        \Illuminate\Support\Facades\Cache::flush();
        // Reset pagination to see all logs
        $this->resetPage();
        $this->dispatch('logs-refreshed');
    }

    public function loadMore()
    {
        $this->perPage += 10;
        $this->clearCache();
    }

    private function clearCache()
    {
        Cache::forget($this->getCacheKey());
        Cache::forget('audit_event_types');
    }

    private function getCacheKey(): string
    {
        return 'audit_logs_' . md5(serialize([
            $this->search,
            $this->eventType,
            $this->severity,
            $this->dateFrom,
            $this->dateTo,
            $this->getPage(),
            $this->perPage
        ]));
    }

    #[Computed]
    public function auditLogs(): LengthAwarePaginator
    {
        return Cache::remember($this->getCacheKey(), 300, function () {
            $query = AuditLog::select(['id', 'action', 'user_id', 'user_type', 'entity_type', 'entity_id', 'old_values', 'new_values', 'ip_address', 'created_at'])
                ->with(['user:id,first_name,last_name'])
                ->orderBy('created_at', 'desc');

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('action', 'like', "%{$this->search}%")
                      ->orWhereHas('user', function ($userQuery) {
                          $userQuery->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$this->search}%"]);
                      });
                });
            }

            if ($this->eventType) {
                $query->where('action', $this->eventType);
            }

            if ($this->severity) {
                $severityActions = $this->getActionsBySeverity($this->severity);
                if (!empty($severityActions)) {
                    $query->whereIn('action', $severityActions);
                }
            }

            if ($this->dateFrom) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            }

            if ($this->dateTo) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            }

            return $query->paginate($this->perPage);
        });
    }

    #[Computed]
    public function eventTypes(): array
    {
        return Cache::remember('audit_event_types', 3600, function () {
            return AuditLog::distinct('action')
                ->pluck('action')
                ->filter()
                ->mapWithKeys(function($action) {
                    return [$action => $this->formatActionLabel($action)];
                })
                ->sort()
                ->toArray();
        });
    }

    #[Computed]
    public function severityLevels(): array
    {
        return ['low', 'medium', 'high', 'critical'];
    }



    private function getActionsBySeverity(string $severity): array
    {
        return match($severity) {
            'high' => ['candidate_rejected', 'user_rejected', 'election_cancelled'],
            'medium' => ['candidate_approved', 'user_approved', 'election_created'],
            'low' => ['user_login', 'user_logout', 'vote_cast'],
            'critical' => ['election_started', 'election_ended', 'system_error'],
            default => []
        };
    }

    private function formatActionLabel(string $action): string
    {
        return ucwords(str_replace('_', ' ', $action));
    }

    public function getSeverity(string $action): string
    {
        return match($action) {
            'candidate_rejected', 'user_rejected', 'election_cancelled' => 'high',
            'candidate_approved', 'user_approved', 'election_created' => 'medium',
            'election_started', 'election_ended', 'system_error' => 'critical',
            default => 'low'
        };
    }

    public function render()
    {
        return view('livewire.observer.audit-logs');
    }
}