<?php

namespace App\Livewire\Observer;

use Livewire\Component;
use App\Models\Observer\ObserverAlert;
use App\Models\Election\Election;

class SubmitAlert extends Component
{
    public $type = '';
    public $severity = 'medium';
    public $title = '';
    public $description = '';
    public $election_id = null;
    public $occurred_at;

    public function mount()
    {
        $this->occurred_at = now()->format('Y-m-d\TH:i');
    }

    public function rules()
    {
        return [
            'type' => 'required|in:security,irregularity,technical,audit,other',
            'severity' => 'required|in:low,medium,high,critical',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'election_id' => 'nullable|exists:elections,id',
            'occurred_at' => 'required|date',
        ];
    }

    public function submit()
    {
        $this->validate();

        ObserverAlert::create([
            'observer_id' => auth('observer')->id(),
            'election_id' => $this->election_id ?: null,
            'type' => $this->type,
            'severity' => $this->severity,
            'status' => 'active',
            'title' => $this->title,
            'description' => $this->description,
            'occurred_at' => $this->occurred_at,
        ]);

        session()->flash('success', 'Alert submitted successfully. Administrators have been notified.');
        $this->reset(['title', 'description', 'election_id']);
        $this->type = '';
        $this->severity = 'medium';
        $this->occurred_at = now()->format('Y-m-d\TH:i');
    }

    public function render()
    {
        return view('livewire.observer.submit-alert', [
            'elections' => Election::whereIn('status', ['active', 'scheduled'])->get()
        ]);
    }
}