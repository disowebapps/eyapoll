<?php

namespace App\Observers;

use App\Models\User;
use App\Services\NavigationCacheService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    protected NavigationCacheService $navigationCache;

    public function __construct(NavigationCacheService $navigationCache)
    {
        $this->navigationCache = $navigationCache;
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Only clear cache if status changed
        if ($user->wasChanged('status')) {
            Log::info('User status updated, clearing navigation cache', [
                'user_id' => $user->id,
                'old_status' => $user->getOriginal('status'),
                'new_status' => $user->status,
            ]);

            $this->navigationCache->clearUserNavigationCache($user->id);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Log::info('User deleted, clearing navigation cache', [
            'user_id' => $user->id,
        ]);

        $this->navigationCache->clearUserNavigationCache($user->id);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        Log::info('User restored, clearing navigation cache', [
            'user_id' => $user->id,
        ]);

        $this->navigationCache->clearUserNavigationCache($user->id);
    }
}