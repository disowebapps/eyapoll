<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Enums\Auth\UserStatus;
use App\Services\Audit\AuditService;
use App\Services\Notification\NotificationService;
use App\Services\Auth\UserStatusService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserApprovals extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'pending';
    public $selectedUsers = [];
    public $selectAll = false;
    public $showApprovalModal = false;
    public $showRejectionModal = false;
    public $rejectionReason = '';
    public $bulkAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'pending'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = $this->getUsersQuery()->pluck('id')->toArray();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function approveUser($userId)
    {
        $user = User::findOrFail($userId);
        $admin = auth()->guard('admin')->user();

        try {
            app(UserStatusService::class)->transitionStatus(
                $user,
                UserStatus::APPROVED,
                $admin
            );

            // Send approval notification
            app(NotificationService::class)->sendUserApprovalNotification($user);

            $this->dispatch('toast', type: 'success', message: 'User approved successfully.');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Failed to approve user: ' . $e->getMessage());
        }

        $this->resetSelection();
    }

    public function rejectUser($userId)
    {
        $user = User::findOrFail($userId);
        $admin = auth()->guard('admin')->user();

        if (empty($this->rejectionReason)) {
            $this->dispatch('toast', type: 'error', message: 'Rejection reason is required.');
            return;
        }

        try {
            app(UserStatusService::class)->transitionStatus(
                $user,
                UserStatus::REJECTED,
                $admin,
                ['reason' => $this->rejectionReason]
            );

            // Send rejection notification
            app(NotificationService::class)->sendUserRejectionNotification($user, $this->rejectionReason);

            $this->dispatch('toast', type: 'success', message: 'User rejected successfully.');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Failed to reject user: ' . $e->getMessage());
        }

        $this->resetSelection();
        $this->closeRejectionModal();
    }

    public function bulkApprove()
    {
        if (empty($this->selectedUsers)) {
            $this->dispatch('toast', type: 'error', message: 'No users selected.');
            return;
        }

        $users = User::whereIn('id', $this->selectedUsers)->get();
        $admin = auth()->guard('admin')->user();
        $approvedCount = 0;

        foreach ($users as $user) {
            try {
                app(UserStatusService::class)->transitionStatus(
                    $user,
                    UserStatus::APPROVED,
                    $admin
                );

                // Send approval notification
                app(NotificationService::class)->sendUserApprovalNotification($user);
                $approvedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to approve user in bulk operation', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->dispatch('toast', type: 'success', message: $approvedCount . ' users approved successfully.');
        $this->resetSelection();
    }

    public function bulkReject()
    {
        if (empty($this->selectedUsers)) {
            $this->dispatch('toast', type: 'error', message: 'No users selected.');
            return;
        }

        if (empty($this->rejectionReason)) {
            $this->dispatch('toast', type: 'error', message: 'Rejection reason is required for bulk rejection.');
            return;
        }

        $users = User::whereIn('id', $this->selectedUsers)->get();
        $admin = auth()->guard('admin')->user();
        $rejectedCount = 0;

        foreach ($users as $user) {
            try {
                app(UserStatusService::class)->transitionStatus(
                    $user,
                    UserStatus::REJECTED,
                    $admin,
                    ['reason' => $this->rejectionReason]
                );

                // Send rejection notification
                app(NotificationService::class)->sendUserRejectionNotification($user, $this->rejectionReason);
                $rejectedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to reject user in bulk operation', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->dispatch('toast', type: 'success', message: $rejectedCount . ' users rejected successfully.');
        $this->resetSelection();
        $this->closeRejectionModal();
    }

    public function openRejectionModal()
    {
        $this->showRejectionModal = true;
    }

    public function closeRejectionModal()
    {
        $this->showRejectionModal = false;
        $this->rejectionReason = '';
    }

    private function resetSelection()
    {
        $this->selectedUsers = [];
        $this->selectAll = false;
    }

    private function getUsersQuery()
    {
        $query = User::query()
            ->with(['approvedBy'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query;
    }

    public function getUsersProperty()
    {
        return $this->getUsersQuery()->paginate(20);
    }

    public function getStatsProperty()
    {
        return [
            'pending' => User::where('status', UserStatus::PENDING)->count(),
            'review' => User::where('status', UserStatus::REVIEW)->count(),
            'approved' => User::where('status', UserStatus::APPROVED)->count(),
            'accredited' => User::where('status', UserStatus::ACCREDITED)->count(),
            'rejected' => User::where('status', UserStatus::REJECTED)->count(),
            'suspended' => User::where('status', UserStatus::SUSPENDED)->count(),
            'temporary_hold' => User::where('status', UserStatus::TEMPORARY_HOLD)->count(),
            'expired' => User::where('status', UserStatus::EXPIRED)->count(),
            'renewal_required' => User::where('status', UserStatus::RENEWAL_REQUIRED)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.user-approvals', [
            'users' => $this->users,
            'stats' => $this->stats,
        ]);
    }
}