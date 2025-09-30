<?php

namespace App\Services\Timer;

use App\Models\Election\Election;
use App\Services\Election\ElectionTimeService;
use Carbon\Carbon;

class UnifiedTimerService
{
    public function __construct(
        private ElectionTimeService $timeService
    ) {}

    /**
     * Get server-side countdown data that can't be manipulated
     */
    public function getCountdownData(Election $election): array
    {
        $now = $this->timeService->getCurrentTime();
        $endTime = $election->ends_at;
        
        if ($now->gte($endTime)) {
            $diff = $endTime->diff($now);
            return [
                'ended' => true,
                'display' => 'Ended ' . $this->formatTimeElapsed($diff) . ' ago',
                'seconds_remaining' => 0,
                'server_time' => $now->timestamp,
                'end_time' => $endTime->timestamp,
            ];
        }

        $diff = $now->diff($endTime);
        $totalSeconds = $endTime->timestamp - $now->timestamp;

        return [
            'ended' => false,
            'display' => $this->formatTimeRemaining($diff),
            'seconds_remaining' => $totalSeconds,
            'server_time' => $now->timestamp,
            'end_time' => $endTime->timestamp,
            'days' => $diff->days,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s,
        ];
    }

    /**
     * Format time remaining in consistent format
     */
    private function formatTimeRemaining(\DateInterval $diff): string
    {
        if ($diff->days > 0) {
            return $diff->days . 'd ' . $diff->h . 'h ' . $diff->i . 'm ' . $diff->s . 's';
        } elseif ($diff->h > 0) {
            return $diff->h . 'h ' . $diff->i . 'm ' . $diff->s . 's';
        } elseif ($diff->i > 0) {
            return $diff->i . 'm ' . $diff->s . 's';
        } else {
            return $diff->s . 's';
        }
    }

    /**
     * Format elapsed time since election ended
     */
    private function formatTimeElapsed(\DateInterval $diff): string
    {
        if ($diff->days > 0) {
            return $diff->days . ' day' . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        } else {
            return $diff->s . ' second' . ($diff->s > 1 ? 's' : '');
        }
    }

    /**
     * Get Alpine.js timer configuration
     */
    public function getAlpineTimerConfig(Election $election): array
    {
        $countdown = $this->getCountdownData($election);
        
        return [
            'end_time' => $election->ends_at->format('Y-m-d H:i:s'),
            'ended' => $countdown['ended'],
            'display' => $countdown['display'],
        ];
    }
}