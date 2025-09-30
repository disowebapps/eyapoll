<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\TokenMonitorService;

class TokenMonitor extends Component
{
    use WithPagination;

    public $selectedElection = 'all';
    public $statusFilter = 'all';
    public $search = '';
    
    protected $queryString = ['search', 'selectedElection', 'statusFilter'];

    public function render()
    {
        $service = app(TokenMonitorService::class);
        
        $electionId = $this->selectedElection !== 'all' ? (int)$this->selectedElection : null;
        $stats = $service->getStats($electionId);
        
        $filters = [
            'election_id' => $this->selectedElection,
            'status' => $this->statusFilter,
            'search' => $this->search
        ];
        $tokens = $service->getTokens($filters);
        $elections = $service->getElections();
        
        return view('livewire.admin.token-monitor', compact('stats', 'tokens', 'elections'));
    }
}