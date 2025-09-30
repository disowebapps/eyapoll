<?php

namespace App\Http\Livewire\Admin\Charts;

use Livewire\Component;
use App\Models\Voting\VoteToken;
use App\Models\Election\Election;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class VoterParticipation extends Component
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
            
            $totalVotes = VoteToken::where('is_used', true)
                ->whereBetween('used_at', [$date, $nextDate])
                ->count();

            $data[] = [
                'date' => $date->format('Y-m-d H:i'),
                'votes' => $totalVotes
            ];
        }

        return [
            'labels' => array_column($data, 'date'),
            'datasets' => [
                [
                    'label' => 'Votes Cast',
                    'data' => array_column($data, 'votes'),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
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
        return view('livewire.admin.charts.voter-participation', [
            'chartData' => $this->chartData
        ]);
    }
}