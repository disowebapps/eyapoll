<?php

namespace App\Http\Livewire\Admin\Charts;

use Livewire\Component;
use App\Models\System\SecurityEvent;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SecurityMetrics extends Component
{
    public $period = '24h';
    protected $listeners = ['refreshChart' => '$refresh'];

    public function getChartDataProperty()
    {
        $end = now();
        $start = $this->getStartDate();
        $interval = $this->getInterval();

        $period = CarbonPeriod::create($start, $interval, $end);
        $data = [];

        foreach ($period as $date) {
            $nextDate = $date->copy()->add($interval);
            
            $events = SecurityEvent::whereBetween('created_at', [$date, $nextDate])
                ->selectRaw('severity, count(*) as count')
                ->groupBy('severity')
                ->get()
                ->pluck('count', 'severity')
                ->toArray();

            $data[] = [
                'date' => $date->format('Y-m-d H:i'),
                'high' => $events['high'] ?? 0,
                'medium' => $events['medium'] ?? 0,
                'low' => $events['low'] ?? 0
            ];
        }

        return [
            'labels' => array_column($data, 'date'),
            'datasets' => [
                [
                    'label' => 'High Severity',
                    'data' => array_column($data, 'high'),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
                [
                    'label' => 'Medium Severity',
                    'data' => array_column($data, 'medium'),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
                [
                    'label' => 'Low Severity',
                    'data' => array_column($data, 'low'),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ]
            ]
        ];
    }

    protected function getStartDate()
    {
        return match($this->period) {
            '24h' => now()->subDay(),
            '7d' => now()->subWeek(),
            '30d' => now()->subMonth(),
            default => now()->subDay()
        };
    }

    protected function getInterval()
    {
        return match($this->period) {
            '24h' => '1 hour',
            '7d' => '1 day',
            '30d' => '1 day',
            default => '1 hour'
        };
    }

    public function render()
    {
        return view('livewire.admin.charts.security-metrics', [
            'chartData' => $this->chartData
        ]);
    }
}