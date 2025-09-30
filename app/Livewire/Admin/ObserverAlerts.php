<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Observer\ObserverAlert;

class ObserverAlerts extends Component
{
    use WithPagination;

    public $statusFilter = 'active';
    public $severityFilter = '';

    public function updateStatus($alertId, $status)
    {
        $alert = ObserverAlert::findOrFail($alertId);
        $alert->update([
            'status' => $status,
            'resolved_at' => $status === 'resolved' ? now() : null,
            'assigned_to' => auth('admin')->id(),
        ]);

        session()->flash('success', 'Alert status updated successfully.');
    }

    public function render()
    {
        $query = ObserverAlert::with(['observer', 'election'])
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->severityFilter, fn($q) => $q->where('severity', $this->severityFilter))
            ->orderBy('created_at', 'desc');

        return view('livewire.admin.observer-alerts', [
            'alerts' => $query->paginate(10)
        ]);
    }
}