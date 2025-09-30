<?php

namespace App\Observers;

use App\Models\Election\Election;
use App\Services\NavigationCacheService;
use Illuminate\Support\Facades\Log;

class ElectionObserver
{
    protected NavigationCacheService $navigationCache;

    public function __construct(NavigationCacheService $navigationCache)
    {
        $this->navigationCache = $navigationCache;
    }

    /**
     * Handle the Election "created" event.
     */
    public function created(Election $election): void
    {
        Log::info('Election created, clearing navigation cache', [
            'election_id' => $election->id,
            'status' => $election->status,
        ]);

        $this->navigationCache->clearElectionNavigationCaches();
    }

    /**
     * Handle the Election "updated" event.
     */
    public function updated(Election $election): void
    {
        // Only clear cache if status or timing fields changed
        if ($election->wasChanged(['status', 'starts_at', 'ends_at', 'phase'])) {
            Log::info('Election updated (critical fields), clearing navigation cache', [
                'election_id' => $election->id,
                'changed_fields' => $election->getChanges(),
            ]);

            $this->navigationCache->clearElectionNavigationCaches();
        }
    }

    /**
     * Handle the Election "deleted" event.
     */
    public function deleted(Election $election): void
    {
        Log::info('Election deleted, clearing navigation cache', [
            'election_id' => $election->id,
        ]);

        $this->navigationCache->clearElectionNavigationCaches();
    }

    /**
     * Handle the Election "restored" event.
     */
    public function restored(Election $election): void
    {
        Log::info('Election restored, clearing navigation cache', [
            'election_id' => $election->id,
        ]);

        $this->navigationCache->clearElectionNavigationCaches();
    }

    /**
     * Handle the Election "force deleted" event.
     */
    public function forceDeleted(Election $election): void
    {
        Log::info('Election force deleted, clearing navigation cache', [
            'election_id' => $election->id,
        ]);

        $this->navigationCache->clearElectionNavigationCaches();
    }
}