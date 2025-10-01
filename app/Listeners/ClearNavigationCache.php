<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\Utility\NavigationCacheService;
use App\Events\ElectionStatusChanged;
use App\Events\ElectionCreated;
use App\Events\ElectionUpdated;
use App\Events\ElectionDeleted;
use App\Events\UserStatusChanged;
use Illuminate\Support\Facades\Log;

class ClearNavigationCache implements ShouldQueue
{
    use InteractsWithQueue;

    protected NavigationCacheService $navigationCache;

    /**
     * Create the event listener.
     */
    public function __construct(NavigationCacheService $navigationCache)
    {
        $this->navigationCache = $navigationCache;
    }

    /**
     * Handle election status changes
     */
    public function handleElectionStatusChanged(ElectionStatusChanged $event): void
    {
        Log::info('Clearing navigation cache due to election status change', [
            'election_id' => $event->election->id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
        ]);

        $this->navigationCache->clearElectionNavigationCaches();
    }

    /**
     * Handle election creation
     */
    public function handleElectionCreated(ElectionCreated $event): void
    {
        Log::info('Clearing navigation cache due to election creation', [
            'election_id' => $event->election->id,
        ]);

        $this->navigationCache->clearElectionNavigationCaches();
    }

    /**
     * Handle election updates
     */
    public function handleElectionUpdated(ElectionUpdated $event): void
    {
        Log::info('Clearing navigation cache due to election update', [
            'election_id' => $event->election->id,
        ]);

        $this->navigationCache->clearElectionNavigationCaches();
    }

    /**
     * Handle election deletion
     */
    public function handleElectionDeleted(ElectionDeleted $event): void
    {
        Log::info('Clearing navigation cache due to election deletion', [
            'election_id' => $event->election->id,
        ]);

        $this->navigationCache->clearElectionNavigationCaches();
    }

    /**
     * Handle user status changes
     */
    public function handleUserStatusChanged(UserStatusChanged $event): void
    {
        Log::info('Clearing navigation cache due to user status change', [
            'user_id' => $event->user->id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
        ]);

        $this->navigationCache->clearUserNavigationCache($event->user->id);
    }

    /**
     * Get the name of the listener method for a given event.
     */
    public function getListenerMethodForEvent($event): string
    {
        $eventClass = get_class($event);

        return match ($eventClass) {
            ElectionStatusChanged::class => 'handleElectionStatusChanged',
            ElectionCreated::class => 'handleElectionCreated',
            ElectionUpdated::class => 'handleElectionUpdated',
            ElectionDeleted::class => 'handleElectionDeleted',
            UserStatusChanged::class => 'handleUserStatusChanged',
            default => 'handle',
        };
    }
}