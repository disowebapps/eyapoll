<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Audit\AuditLog;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AuditLogs extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search = '';
    public $actionFilter = 'all';
    public $userTypeFilter = 'all';
    public $dateFrom = '';
    public $dateTo = '';
    public $showFilters = false;

    protected $queryString = ['search', 'actionFilter', 'userTypeFilter', 'dateFrom', 'dateTo'];

    public function mount()
    {
        $this->authorize('viewAny', AuditLog::class);
        $this->dateTo = now()->format('Y-m-d');
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->actionFilter = 'all';
        $this->userTypeFilter = 'all';
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function exportLogs()
    {
        $this->authorize('export', AuditLog::class);
        
        // Implementation would generate CSV/Excel export
        session()->flash('success', 'Audit logs export initiated. Download will start shortly.');
    }

    public function render()
    {
        $logs = AuditLog::query()
            ->when($this->search, function ($query) {
                $query->where('action', 'like', "%{$this->search}%")
                      ->orWhere('description', 'like', "%{$this->search}%")
                      ->orWhere('ip_address', 'like', "%{$this->search}%");
            })
            ->when($this->actionFilter !== 'all', fn($q) => $q->where('action', $this->actionFilter))
            ->when($this->userTypeFilter !== 'all', fn($q) => $q->where('user_type', $this->userTypeFilter))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $stats = [
            'total_logs' => AuditLog::count(),
            'today_logs' => AuditLog::whereDate('created_at', today())->count(),
            'failed_logins' => AuditLog::where('action', 'failed_login')
                                     ->whereDate('created_at', today())
                                     ->count(),
            'admin_actions' => AuditLog::where('user_type', 'admin')
                                     ->whereDate('created_at', today())
                                     ->count(),
        ];

        $availableActions = AuditLog::distinct()->pluck('action')->sort();
        $availableUserTypes = AuditLog::distinct()->pluck('user_type')->sort();

        return view('livewire.admin.audit-logs', compact('logs', 'stats', 'availableActions', 'availableUserTypes'));
    }
}