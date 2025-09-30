<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Candidate\Candidate;
use App\Services\Candidate\CandidateService;
use Illuminate\Support\Facades\Auth;

class PaymentReview extends Component
{
    public Candidate $candidate;
    public $showModal = false;
    public $action = '';
    public $reason = '';
    public $selectedProof = null;

    protected $rules = [
        'reason' => 'required|string|min:10|max:500'
    ];

    public function mount(Candidate $candidate)
    {
        $this->candidate = $candidate->load('paymentProofs', 'paymentHistory.admin');
    }

    public function openModal($action)
    {
        $this->action = $action;
        $this->reason = '';
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reason = '';
        $this->resetValidation();
    }

    public function confirmPayment(CandidateService $service)
    {
        $this->validate();
        
        try {
            $admin = Auth::guard('admin')->user();
            $service->confirmPayment($this->candidate, $admin, $this->reason);
            
            $this->candidate->refresh();
            $this->closeModal();
            session()->flash('success', 'Payment confirmed successfully');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function waivePayment(CandidateService $service)
    {
        $this->validate();
        
        try {
            $admin = Auth::guard('admin')->user();
            $service->waivePayment($this->candidate, $admin, $this->reason);
            
            $this->candidate->refresh();
            $this->closeModal();
            session()->flash('success', 'Payment waived successfully');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function resetPayment(CandidateService $service)
    {
        $this->validate();
        
        try {
            $admin = Auth::guard('admin')->user();
            $service->resetPayment($this->candidate, $admin, $this->reason);
            
            $this->candidate->refresh();
            $this->closeModal();
            session()->flash('success', 'Payment reset successfully');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.payment-review');
    }
}