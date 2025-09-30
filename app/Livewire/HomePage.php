<?php

namespace App\Livewire;

use Livewire\Component;

class Homepage extends Component
{
    public $activeCard = null;
    public $stats = [
        'total_votes' => 15420,
        'active_elections' => 3,
        'verified_voters' => 8950,
        'transparency_score' => 99.8
    ];

    public function selectCard($cardId)
    {
        $this->activeCard = $this->activeCard === $cardId ? null : $cardId;
    }

    public function render()
    {
        return view('livewire.homepage')->layout('layouts.guest');
    }
}