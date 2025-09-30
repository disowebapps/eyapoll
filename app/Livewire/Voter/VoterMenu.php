<?php

namespace App\Livewire\Voter;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Election\Election;

class VoterMenu extends Component
{
    public $userStatus = [];
    public $navItems = [];

    public function mount()
    {
        $this->initializeNavigation();
        $this->loadUserStatus();
    }

    private function initializeNavigation()
    {
        $this->navItems = [
            [
                'route' => 'voter.dashboard',
                'label' => 'Home',
                'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'active' => $this->isActiveRoute(['voter.dashboard']),
                'badge' => null,
            ],
            [
                'route' => 'voter.elections',
                'label' => 'Elections',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'active' => $this->isActiveRoute(['voter.elections']),
                'badge' => $this->getActiveElectionsCount(),
            ],
            [
                'route' => 'voter.history',
                'label' => 'History',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'active' => $this->isActiveRoute(['voter.history']),
                'badge' => null,
            ],
            [
                'route' => 'voter.profile',
                'label' => 'Profile',
                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'active' => $this->isActiveRoute(['voter.profile']),
                'badge' => $this->getProfileBadge(),
            ],
        ];
    }

    private function isActiveRoute($routes)
    {
        return in_array(request()->route()->getName(), $routes);
    }

    private function getActiveElectionsCount()
    {
        $count = Election::where('status', 'active')
            ->where('ends_at', '>', now())
            ->count();

        \Log::info('Active elections count', ['count' => $count]);

        return $count > 0 ? $count : null;
    }

    private function getProfileBadge()
    {
        $user = Auth::user();

        if ($user->status->value !== 'active') {
            return 'verify';
        }

        return null;
    }

    private function loadUserStatus()
    {
        $user = Auth::user();

        $this->userStatus = [
            'kyc_verified' => $user->status->value === 'active',
            'has_upcoming_elections' => Election::where('status', 'active')
                ->where('ends_at', '>', now())
                ->exists(),
            'has_voting_history' => $user->voteTokens()->where('is_used', true)->exists(),
        ];
    }

    public function render()
    {
        return view('livewire.voter.voter-menu');
    }
}