<?php

namespace App\Livewire\Admin\Notification;

use App\Models\Notification\Notification;
use App\Models\NotificationLog;
use App\Enums\Notification\NotificationChannel;
use App\Enums\Notification\NotificationStatus;
use App\Livewire\Admin\BaseAdminComponent;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

class NotificationLogs extends BaseAdminComponent
{
    use WithPagination;

    public $search = '';
    public $channelFilter = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 25;

    protected $queryString = [
        'search' => ['except' => ''],
        'channelFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        $this->authorize('viewAny', Notification::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingChannelFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->channelFilter = '';
        $this->statusFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function getLogs()
    {
        return NotificationLog::query()
            ->with(['notification.notifiable'])
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('recipient_email', 'like', '%' . $this->search . '%')
                      ->orWhere('recipient_phone', 'like', '%' . $this->search . '%')
                      ->orWhere('message', 'like', '%' . $this->search . '%')
                      ->orWhereHas('notification', function (Builder $q) {
                          $q->where('type', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->channelFilter, function (Builder $query) {
                $query->where('channel', $this->channelFilter);
            })
            ->when($this->statusFilter, function (Builder $query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->dateFrom, function (Builder $query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function (Builder $query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getStats()
    {
        $total = NotificationLog::count();
        $sent = NotificationLog::where('status', 'sent')->count();
        $failed = NotificationLog::where('status', 'failed')->count();
        $delivered = NotificationLog::where('status', 'delivered')->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'delivered' => $delivered,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 1) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.admin.notification.notification-logs', [
            'logs' => $this->getLogs(),
            'stats' => $this->getStats(),
            'channels' => NotificationChannel::cases(),
            'statuses' => ['sent', 'failed', 'delivered', 'pending'],
        ]);
    }
}