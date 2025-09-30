<?php

namespace App\Livewire\Admin\Elections;

use App\Models\Election\Election;
use App\Models\Candidate\Candidate;
use App\Livewire\Admin\BaseAdminComponent;

class Show extends BaseAdminComponent
{

    public Election $election;
    public $activeTab = 'overview';
    public $showEndElectionModal = false;
    public $showStartElectionModal = false;

    public function mount($electionId)
    {
        $this->election = Election::with(['positions', 'candidates', 'voteRecords'])->findOrFail($electionId);
        $this->authorize('view', $this->election);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function approveCandidate($candidateId)
    {
        $candidate = Candidate::findOrFail($candidateId);
        $this->authorize('update', $candidate);

        $candidate->update(['status' => 'approved']);
        session()->flash('success', 'Candidate approved successfully.');
    }

    public function rejectCandidate($candidateId)
    {
        $candidate = Candidate::findOrFail($candidateId);
        $this->authorize('update', $candidate);

        $candidate->update(['status' => 'rejected']);
        session()->flash('success', 'Candidate rejected.');
    }

    // REMOVED: Unauthorized token generation method
    // Tokens can ONLY be generated through:
    // 1. Manual accreditation process
    // 2. Voter register publication

    public function openEndElectionModal()
    {
        $admin = auth('admin')->user();
        
        // Check if admin has permission to end elections
        if (!$admin->hasPermission('manage-elections') && !$admin->hasPermission('super-admin')) {
            session()->flash('error', 'You do not have permission to end elections.');
            return;
        }
        
        $this->showEndElectionModal = true;
    }
    
    public function closeEndElectionModal()
    {
        $this->showEndElectionModal = false;
    }
    
    public function openStartElectionModal()
    {
        $admin = auth('admin')->user();
        
        if (!$admin->hasPermission('manage-elections') && !$admin->hasPermission('super-admin')) {
            session()->flash('error', 'You do not have permission to start elections.');
            return;
        }
        
        $this->showStartElectionModal = true;
    }
    
    public function closeStartElectionModal()
    {
        $this->showStartElectionModal = false;
    }

    public function confirmStartElection()
    {
        try {
            $admin = auth('admin')->user();
            
            if (!$admin->hasPermission('manage-elections') && !$admin->hasPermission('super-admin')) {
                throw new \UnauthorizedAccessException('Insufficient permissions to start election');
            }
            
            \Illuminate\Support\Facades\Log::critical('CRITICAL ACTION: Election start initiated', [
                'election_id' => $this->election->id,
                'election_title' => $this->election->title,
                'admin_id' => $admin->id,
                'admin_name' => $admin->getFullNameAttribute(),
                'admin_email' => $admin->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString()
            ]);
            
            app(\App\Services\Election\ElectionService::class)
                ->startElection($this->election, $admin);
            
            \Illuminate\Support\Facades\Log::critical('CRITICAL ACTION: Election started successfully', [
                'election_id' => $this->election->id,
                'admin_id' => $admin->id,
                'timestamp' => now()->toISOString()
            ]);
            
            session()->flash('success', 'Election started successfully.');
            $this->election = $this->election->fresh();
            $this->closeStartElectionModal();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('CRITICAL ACTION FAILED: Election start failed', [
                'election_id' => $this->election->id,
                'admin_id' => auth('admin')->id(),
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ]);
            
            session()->flash('error', $e->getMessage());
            $this->closeStartElectionModal();
        }
    }

    public function confirmEndElection()
    {
        try {
            $admin = auth('admin')->user();
            
            // Double-check permissions
            if (!$admin->hasPermission('manage-elections') && !$admin->hasPermission('super-admin')) {
                throw new \UnauthorizedAccessException('Insufficient permissions to end election');
            }
            
            // Log the critical action attempt
            \Illuminate\Support\Facades\Log::critical('CRITICAL ACTION: Election end initiated', [
                'election_id' => $this->election->id,
                'election_title' => $this->election->title,
                'admin_id' => $admin->id,
                'admin_name' => $admin->getFullNameAttribute(),
                'admin_email' => $admin->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString()
            ]);
            
            app(\App\Services\Election\ElectionService::class)
                ->endElection($this->election, $admin);
            
            // Log successful completion
            \Illuminate\Support\Facades\Log::critical('CRITICAL ACTION: Election ended successfully', [
                'election_id' => $this->election->id,
                'admin_id' => $admin->id,
                'timestamp' => now()->toISOString()
            ]);
            
            session()->flash('success', 'Election ended successfully.');
            $this->election = $this->election->fresh();
            $this->closeEndElectionModal();
        } catch (\Exception $e) {
            // Log the failure
            \Illuminate\Support\Facades\Log::error('CRITICAL ACTION FAILED: Election end failed', [
                'election_id' => $this->election->id,
                'admin_id' => auth('admin')->id(),
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ]);
            
            session()->flash('error', $e->getMessage());
            $this->closeEndElectionModal();
        }
    }

    public function render()
    {
        $stats = [
            'total_positions' => $this->election->positions->count(),
            'total_candidates' => $this->election->candidates->count(),
            'approved_candidates' => $this->election->candidates->where('status', 'approved')->count(),
            'pending_candidates' => $this->election->candidates->where('status', 'pending')->count(),
            'total_votes' => $this->election->voteRecords ? $this->election->voteRecords->count() : 0,
            'voter_turnout' => $this->election->getVoterTurnout(),
        ];

        return view('livewire.admin.elections.show', compact('stats'));
    }
}