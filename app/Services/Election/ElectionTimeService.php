<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Enums\Election\ElectionStatus;
use App\Exceptions\InvalidElectionDatesException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ElectionTimeService
{
    private const NTP_SERVERS = [
        'time.google.com',
        'pool.ntp.org',
        'time.cloudflare.com'
    ];

    public function getCurrentTime(): Carbon
    {
        return $this->getTrustedTime() ?? Carbon::now('UTC');
    }

    public function getElectionStatus(Election $election): ElectionStatus
    {
        $now = $this->getCurrentTime();

        if ($election->status === ElectionStatus::CANCELLED) {
            return ElectionStatus::CANCELLED;
        }

        if (!$election->starts_at || !$election->ends_at) {
            Log::warning('Election has null dates', [
                'election_id' => $election->id,
                'election_title' => $election->title,
                'starts_at' => $election->starts_at,
                'ends_at' => $election->ends_at,
            ]);
            throw new InvalidElectionDatesException("Election '{$election->title}' has missing or invalid dates", $election);
        }

        if ($election->ends_at && $now->gte($election->ends_at)) {
            return ElectionStatus::COMPLETED;
        }

        if ($election->starts_at && $now->gte($election->starts_at)) {
            return ElectionStatus::ONGOING;
        }

        return ElectionStatus::UPCOMING;
    }

    public function canAcceptVotes(Election $election): bool
    {
        try {
            return $this->getElectionStatus($election) === ElectionStatus::ONGOING;
        } catch (InvalidElectionDatesException $e) {
            return false;
        }
    }

    private function getTrustedTime(): ?Carbon
    {
        return Cache::remember('trusted_time', 30, function () {
            foreach (self::NTP_SERVERS as $server) {
                try {
                    $response = Http::timeout(2)->get("http://worldtimeapi.org/api/timezone/UTC");
                    if ($response->successful()) {
                        return Carbon::parse($response->json('utc_datetime'));
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            return null;
        });
    }
}