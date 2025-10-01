<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Events\Election\ElectionStarted;
use App\Events\Election\ElectionEnded;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class ElectionService
{
    public function getActiveElections(): Collection
    {
        return Election::active()->get();
    }

    public function startElection(Election $election, $admin = null): bool
    {
        if (!$election->canBeStarted()) {
            throw new \InvalidArgumentException('Election cannot be started at this time.');
        }

        return DB::transaction(function() use ($election, $admin) {
            $oldStatus = $election->status->value;
            $success = $election->update(['status' => \App\Enums\Election\ElectionStatus::ONGOING->value, 'started_at' => now()]);

            if ($success) {
                // Vote tokens should be generated manually through accreditation process
                $tokensGenerated = 0;

                // Fire election started event
                Event::dispatch(new ElectionStarted($election, $admin));

                // Create audit log entry
                try {
                    app(\App\Services\Audit\AuditLogService::class)->log(
                        'election_started',
                        $admin,
                        Election::class,
                        $election->id,
                        ['status' => $oldStatus],
                        ['status' => \App\Enums\Election\ElectionStatus::ONGOING->value, 'started_at' => now()->toISOString(), 'tokens_generated' => $tokensGenerated]
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Audit logging failed for election start', [
                        'election_id' => $election->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return $success;
        });
    }

    public function endElection(Election $election, $admin): bool
    {
        if (!$election->canBeEnded()) {
            throw new \InvalidArgumentException('Election cannot be ended at this time.');
        }

        return DB::transaction(function() use ($election, $admin) {
            $oldStatus = $election->status->value;
            $success = $election->update(['status' => \App\Enums\Election\ElectionStatus::COMPLETED->value, 'ended_at' => now()]);

            if ($success) {
                // Fire election ended event
                Event::dispatch(new ElectionEnded($election, $admin));

                // Create audit log entry
                try {
                    app(\App\Services\Audit\AuditLogService::class)->log(
                        'election_ended',
                        $admin,
                        Election::class,
                        $election->id,
                        ['status' => $oldStatus],
                        ['status' => \App\Enums\Election\ElectionStatus::COMPLETED->value, 'ended_at' => now()->toISOString()]
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Audit logging failed for election end', [
                        'election_id' => $election->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return $success;
        });
    }
}
