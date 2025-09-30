<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Election\Election;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmergencyControls extends Component
{
    public $showModal = false;
    public $selectedElection = null;
    public $reason = '';
    public $adminPassword = '';
    public $confirmText = '';
    public $step = 1;
    public $auditHash = '';

    public function openHaltModal($electionId)
    {
        $this->selectedElection = Election::findOrFail($electionId);
        $this->showModal = true;
        $this->step = 1;
        $this->auditHash = hash('sha256', $electionId . now()->timestamp . auth('admin')->id());
    }

    public function nextStep()
    {
        if ($this->step == 1) {
            $this->validate(['reason' => 'required|min:20|max:500']);
            $this->step = 2;
        } elseif ($this->step == 2) {
            $this->validate([
                'adminPassword' => 'required',
                'confirmText' => 'required|in:EMERGENCY HALT CONFIRMED'
            ]);
            
            if (!Hash::check($this->adminPassword, auth('admin')->user()->password)) {
                $this->addError('adminPassword', 'Invalid password');
                return;
            }
            $this->step = 3;
        }
    }

    public function executeHalt()
    {
        $admin = auth('admin')->user();
        $election = $this->selectedElection;
        
        // Tamper-evident audit trail
        app(AuditLogService::class)->log(
            'emergency_halt_initiated',
            $admin,
            Election::class,
            $election->id,
            [
                'previous_status' => $election->status,
                'audit_hash' => $this->auditHash,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString()
            ],
            [
                'reason' => $this->reason,
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'verification_hash' => hash('sha256', $this->reason . $admin->id . $election->id)
            ]
        );

        $election->update(['status' => 'emergency_halted']); // This is a special status, not in enum
        
        app(AuditLogService::class)->log(
            'emergency_halt_completed',
            $admin,
            Election::class,
            $election->id,
            ['status' => 'emergency_halted'],
            ['audit_hash' => $this->auditHash]
        );

        session()->flash('success', 'Election emergency halt executed');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['reason', 'adminPassword', 'confirmText', 'step', 'selectedElection', 'auditHash']);
    }

    public function render()
    {
        return view('livewire.admin.emergency-controls', [
            'elections' => Election::whereIn('status', [\App\Enums\Election\ElectionStatus::ONGOING->value])->get()
        ]);
    }
}