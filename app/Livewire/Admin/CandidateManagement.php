<?php

namespace App\Livewire\Admin;

use Livewire\WithPagination;
use App\Models\Candidate\Candidate;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Services\Candidate\CandidateNotificationService;

class CandidateManagement extends BaseAdminComponent
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $electionFilter = 'all';
    public $positionFilter = 'all';
    public $showBulkActions = false;
    public $selectedCandidates = [];
    public $bulkAction = '';
    public $showCandidateModal = false;
    public $selectedCandidate = null;

    public function mount()
    {
        $this->authorize('viewAny', Candidate::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleCandidateSelection($candidateId)
    {
        if (in_array($candidateId, $this->selectedCandidates)) {
            $this->selectedCandidates = array_diff($this->selectedCandidates, [$candidateId]);
        } else {
            $this->selectedCandidates[] = $candidateId;
        }
        
        $this->showBulkActions = !empty($this->selectedCandidates);
    }

    public function selectAllCandidates()
    {
        $candidates = $this->getCandidatesQuery()->pluck('id')->toArray();
        $this->selectedCandidates = $candidates;
        $this->showBulkActions = !empty($this->selectedCandidates);
    }

    public function clearSelection()
    {
        $this->selectedCandidates = [];
        $this->showBulkActions = false;
    }

    public function executeBulkAction()
    {
        if (empty($this->selectedCandidates) || empty($this->bulkAction)) {
            return;
        }

        $this->authorize('update', Candidate::class);

        $count = 0;
        foreach ($this->selectedCandidates as $candidateId) {
            $candidate = Candidate::find($candidateId);
            if ($candidate && $this->canUpdateCandidateStatus($candidate)) {
                $candidate->update(['status' => $this->bulkAction]);
                $count++;
            }
        }

        $this->dispatch('toast', type: 'success', message: "Updated status for {$count} candidates.");
        $this->clearSelection();
    }

    public function approveCandidate($candidateId)
    {
        $candidate = Candidate::findOrFail($candidateId);
        $this->authorize('update', $candidate);

        if (!$this->canUpdateCandidateStatus($candidate)) {
            $this->dispatch('toast', type: 'error', message: 'Cannot approve this candidate at this time.');
            return;
        }

        // Check if required documents are uploaded and approved
        if (!$candidate->hasUploadedRequiredDocuments()) {
            $this->dispatch('toast', type: 'error', message: 'Cannot approve candidate: Required documents not uploaded or approved.');
            return;
        }

        $candidate->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth('admin')->id(),
        ]);

        // Audit log approval
        app(\App\Services\Audit\AuditLogService::class)->log(
            'candidate_approved',
            auth('admin')->user(),
            \App\Models\Candidate\Candidate::class,
            $candidate->id,
            ['status' => $candidate->getOriginal('status')],
            ['status' => 'approved', 'approved_at' => now(), 'approved_by' => auth('admin')->id()]
        );

        // Send notification
        $notificationService = app(CandidateNotificationService::class);
        $notificationService->notifyApplicationApproved($candidate);

        $this->dispatch('toast', type: 'success', message: 'Candidate approved successfully.');
        $this->closeCandidateModal();
    }

    public function rejectCandidate($candidateId, $reason = null)
    {
        $candidate = Candidate::findOrFail($candidateId);
        $this->authorize('update', $candidate);

        $candidate->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => auth('admin')->id(),
        ]);

        // Audit log rejection
        app(\App\Services\Audit\AuditLogService::class)->log(
            'candidate_rejected',
            auth('admin')->user(),
            \App\Models\Candidate\Candidate::class,
            $candidate->id,
            ['status' => $candidate->getOriginal('status')],
            ['status' => 'rejected', 'rejection_reason' => $reason, 'approved_by' => auth('admin')->id()]
        );

        // Send notification
        $notificationService = app(CandidateNotificationService::class);
        $notificationService->notifyApplicationRejected($candidate);

        $this->dispatch('toast', type: 'success', message: 'Candidate rejected.');
        $this->closeCandidateModal();
    }

    public function suspendCandidate($candidateId, $reason = null)
    {
        $candidate = Candidate::findOrFail($candidateId);
        $this->authorize('update', $candidate);

        $candidate->update([
            'status' => 'suspended',
            'suspension_reason' => $reason,
            'approved_by' => auth('admin')->id(),
        ]);

        // Audit log suspension
        app(\App\Services\Audit\AuditLogService::class)->log(
            'candidate_suspended',
            auth('admin')->user(),
            \App\Models\Candidate\Candidate::class,
            $candidate->id,
            ['status' => $candidate->getOriginal('status')],
            ['status' => 'suspended', 'suspension_reason' => $reason, 'approved_by' => auth('admin')->id()]
        );

        $this->dispatch('toast', type: 'success', message: 'Candidate suspended.');
        $this->closeCandidateModal();
    }

    public function viewCandidate($candidateId)
    {
        return redirect()->route('admin.candidates.show', $candidateId);
    }

    public function addCandidate()
    {
        // Get elections that can accept candidate applications
        $activeElection = \App\Models\Election\Election::where('status', 'upcoming')
            ->orderBy('starts_at', 'asc')
            ->first();
            
        if (!$activeElection) {
            $this->dispatch('toast', type: 'error', message: 'No upcoming elections available for candidate applications.');
            return;
        }
        
        // Audit log admin-initiated candidate creation
        app(\App\Services\Audit\AuditLogService::class)->log(
            'admin_initiated_candidate_creation',
            auth('admin')->user(),
            \App\Models\Election\Election::class,
            $activeElection->id,
            null,
            [
                'redirect_route' => 'candidate.apply',
                'initiated_from' => 'admin.candidates.index',
            ]
        );
        
        // Redirect to existing candidate application flow
        return redirect()->route('candidate.apply', $activeElection->id);
    }

    public function closeCandidateModal()
    {
        $this->showCandidateModal = false;
        $this->selectedCandidate = null;
    }

    public function unsuspendCandidate($candidateId)
    {
        $candidate = Candidate::findOrFail($candidateId);
        $this->authorize('update', $candidate);

        $candidate->update([
            'status' => 'approved',
            'suspension_reason' => null,
            'approved_by' => auth('admin')->id(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Candidate unsuspended.');
        $this->closeCandidateModal();
    }

    private function canUpdateCandidateStatus($candidate)
    {
        // Cannot update if election is active and candidate is already approved
        if ($candidate->election && $candidate->election->isActive() && $candidate->status === 'approved') {
            return false;
        }
        
        return true;
    }

    private function getCandidatesQuery()
    {
        return Candidate::with(['election', 'position', 'user'])
            ->when($this->search, fn($q) => $q->whereHas('user', function($query) {
                $query->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->electionFilter !== 'all', fn($q) => $q->where('election_id', $this->electionFilter))
            ->when($this->positionFilter !== 'all', fn($q) => $q->where('position_id', $this->positionFilter))
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $candidates = $this->getCandidatesQuery()->paginate(20);
        
        $elections = Election::orderBy('title')->get();
        $positions = Position::with('election')->orderBy('title')->get();

        $stats = [
            'total' => Candidate::count(),
            'pending' => Candidate::where('status', 'pending')->count(),
            'approved' => Candidate::where('status', 'approved')->count(),
            'rejected' => Candidate::where('status', 'rejected')->count(),
            'suspended' => Candidate::where('status', 'suspended')->count(),
        ];

        return view('livewire.admin.candidate-management', compact('candidates', 'elections', 'positions', 'stats'));
    }
}