<?php

namespace App\Livewire\Admin\Elections;

use Livewire\WithPagination;
use App\Models\Election\Election;
use App\Enums\Election\ElectionStatus;
use App\Livewire\Admin\BaseAdminComponent;

class Index extends BaseAdminComponent
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    protected $queryString = ['search', 'statusFilter'];

    public function mount()
    {
        $this->authorize('viewAny', Election::class);
        
        $status = request('status');
        if ($status && in_array($status, ['active', 'scheduled', 'ended', 'cancelled'])) {
            $this->statusFilter = $status;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function startElection($electionId)
    {
        $election = Election::findOrFail($electionId);
        $this->authorize('update', $election);

        if (!$election->canBeStarted()) {
            session()->flash('error', 'Election cannot be started at this time.');
            return;
        }

        $election->update(['status' => ElectionStatus::ONGOING]);
        // SECURITY: Tokens can ONLY be generated through accreditation or voter register publication
        
        session()->flash('success', 'Election started successfully.');
    }

    public function endElection($electionId)
    {
        $election = Election::findOrFail($electionId);
        $this->authorize('update', $election);

        if (!$election->canBeEnded()) {
            session()->flash('error', 'Election cannot be ended at this time.');
            return;
        }

        $election->update(['status' => ElectionStatus::COMPLETED]);
        session()->flash('success', 'Election ended successfully.');
    }

    public function cancelElection($electionId)
    {
        $election = Election::findOrFail($electionId);
        $this->authorize('delete', $election);

        if (!$election->canBeCancelled()) {
            session()->flash('error', 'Election cannot be cancelled at this time.');
            return;
        }

        $election->update(['status' => ElectionStatus::CANCELLED]);
        session()->flash('success', 'Election cancelled successfully.');
    }

    public function render()
    {
        $elections = Election::with('positions')
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->orderByRaw("CASE
                WHEN status = 'ongoing' THEN 1
                WHEN status = 'completed' THEN 2
                WHEN status = 'upcoming' THEN 3
                ELSE 4 END")
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);

        return view('livewire.admin.elections.index', compact('elections'));
    }
}