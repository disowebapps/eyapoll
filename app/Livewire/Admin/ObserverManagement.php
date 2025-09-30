<?php

namespace App\Livewire\Admin;

use Livewire\WithPagination;
use App\Models\Observer;
use App\Models\Election\Election;

class ObserverManagement extends BaseAdminComponent
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $typeFilter = 'all';
    public $showObserverModal = false;
    public $showAssignModal = false;
    public $showPrivilegeModal = false;
    public $selectedObserver = null;
    public $selectedElections = [];
    public $selectedPrivileges = [];
    public $suspensionReason = '';
    public $revocationReason = '';
    public $showCreateModal = false;
    public $newObserver = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone_number' => '',
        'organization_name' => '',
        'type' => 'independent',
        'password' => '',
        'privileges' => [],
    ];

    public function mount()
    {
        $this->authorize('viewAny', Observer::class);
    }

    public function viewObserver($observerId)
    {
        return redirect()->route('admin.observers.show', $observerId);
    }

    public function closeObserverModal()
    {
        $this->showObserverModal = false;
        $this->selectedObserver = null;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->typeFilter = 'all';
    }

    public function exportObservers()
    {
        // Export functionality
        session()->flash('success', 'Observer export initiated.');
    }

    public function addObserver()
    {
        $this->showCreateModal = true;
        $this->resetNewObserver();
    }

    public function resetNewObserver()
    {
        $this->newObserver = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone_number' => '',
            'organization_name' => '',
            'type' => 'independent',
            'password' => '',
            'privileges' => [],
        ];
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetNewObserver();
    }

    public function createObserver()
    {
        $this->validate([
            'newObserver.first_name' => 'required|string|max:255',
            'newObserver.last_name' => 'required|string|max:255',
            'newObserver.email' => 'required|email|unique:observers,email',
            'newObserver.phone_number' => 'required|string|max:20',
            'newObserver.organization_name' => 'nullable|string|max:255',
            'newObserver.type' => 'required|in:independent,organization',
            'newObserver.password' => 'required|string|min:8',
            'newObserver.privileges' => 'array',
        ]);

        Observer::create([
            'first_name' => $this->newObserver['first_name'],
            'last_name' => $this->newObserver['last_name'],
            'email' => $this->newObserver['email'],
            'phone_number' => $this->newObserver['phone_number'],
            'organization_name' => $this->newObserver['organization_name'],
            'type' => $this->newObserver['type'],
            'password' => bcrypt($this->newObserver['password']),
            'status' => 'approved',
            'observer_privileges' => $this->newObserver['privileges'],
            'approved_at' => now(),
            'approved_by' => auth('admin')->id(),
        ]);

        session()->flash('success', 'Observer created successfully.');
        $this->closeCreateModal();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function approveObserver($observerId)
    {
        $observer = Observer::findOrFail($observerId);
        $this->authorize('update', $observer);

        $observer->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth('admin')->id(),
        ]);

        $this->selectedObserver = $observer->fresh();
        session()->flash('success', 'Observer approved successfully.');
    }

    public function rejectObserver($observerId)
    {
        $observer = Observer::findOrFail($observerId);
        $this->authorize('update', $observer);

        $observer->update([
            'status' => 'rejected',
            'approved_by' => auth('admin')->id(),
        ]);

        $this->selectedObserver = $observer->fresh();
        session()->flash('success', 'Observer rejected.');
    }

    public function suspendObserver($observerId)
    {
        $observer = Observer::findOrFail($observerId);
        $this->authorize('update', $observer);

        $observer->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_by' => auth('admin')->id(),
            'suspension_reason' => 'Suspended by admin',
        ]);

        $this->selectedObserver = $observer->fresh();
        session()->flash('success', 'Observer suspended.');
    }

    public function unsuspendObserver($observerId)
    {
        $observer = Observer::findOrFail($observerId);
        $this->authorize('update', $observer);

        $observer->update([
            'status' => 'approved',
            'suspended_at' => null,
            'suspended_by' => null,
            'suspension_reason' => null,
        ]);

        $this->selectedObserver = $observer->fresh();
        session()->flash('success', 'Observer unsuspended.');
    }

    public function revokeObserver($observerId)
    {
        $observer = Observer::findOrFail($observerId);
        $this->authorize('update', $observer);

        $observer->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revoked_by' => auth('admin')->id(),
            'revocation_reason' => 'Access revoked by admin',
        ]);

        $this->selectedObserver = $observer->fresh();
        session()->flash('success', 'Observer access revoked.');
    }

    public function unrevokeObserver($observerId)
    {
        $observer = Observer::findOrFail($observerId);
        $this->authorize('update', $observer);

        $observer->update([
            'status' => 'approved',
            'revoked_at' => null,
            'revoked_by' => null,
            'revocation_reason' => null,
        ]);

        $this->selectedObserver = $observer->fresh();
        session()->flash('success', 'Observer access restored.');
    }

    public function openAssignModal($observerId)
    {
        $this->selectedObserver = $observerId;
        $observer = Observer::with('assignedElections')->find($observerId);
        $this->selectedElections = $observer->assignedElections->pluck('id')->toArray();
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->selectedObserver = null;
        $this->selectedElections = [];
    }

    public function updateElectionAssignments()
    {
        $observer = Observer::findOrFail($this->selectedObserver);
        $this->authorize('update', $observer);

        $observer->assignedElections()->sync($this->selectedElections);
        
        session()->flash('success', 'Election assignments updated successfully.');
        $this->closeAssignModal();
    }

    public function openPrivilegeModal($observerId)
    {
        $this->selectedObserver = $observerId;
        $observer = Observer::find($observerId);
        $this->selectedPrivileges = $observer->observer_privileges ?? [];
        $this->showPrivilegeModal = true;
    }

    public function closePrivilegeModal()
    {
        $this->showPrivilegeModal = false;
        $this->selectedObserver = null;
        $this->selectedPrivileges = [];
    }

    public function updatePrivileges()
    {
        $observer = Observer::findOrFail($this->selectedObserver);
        $this->authorize('update', $observer);

        $observer->update(['observer_privileges' => $this->selectedPrivileges]);
        
        session()->flash('success', 'Observer privileges updated successfully.');
        $this->closePrivilegeModal();
    }

    public function render()
    {
        $observers = Observer::query()
            ->when($this->search, fn($q) => $q->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('organization_name', 'like', "%{$this->search}%"))
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter !== 'all', fn($q) => $q->where('type', $this->typeFilter))
            ->orderBy('created_at', 'desc')
            ->get();

        $elections = Election::where('status', '!=', 'cancelled')
            ->orderBy('title')
            ->get();

        $stats = [
            'total' => Observer::count(),
            'pending' => Observer::where('status', 'pending')->count(),
            'approved' => Observer::where('status', 'approved')->count(),
            'rejected' => Observer::where('status', 'rejected')->count(),
            'suspended' => Observer::where('status', 'suspended')->count(),
            'revoked' => Observer::where('status', 'revoked')->count(),
        ];

        $availablePrivileges = Observer::getAvailablePrivileges();

        return view('livewire.admin.observer-management', compact('observers', 'elections', 'stats', 'availablePrivileges'));
    }
}