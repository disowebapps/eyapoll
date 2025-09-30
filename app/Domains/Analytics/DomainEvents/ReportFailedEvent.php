<?php

namespace App\Domains\Analytics\DomainEvents;

use App\Domains\Analytics\Entities\AnalyticsReport;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportFailedEvent
{
    use Dispatchable, SerializesModels;

    public AnalyticsReport $report;

    public function __construct(AnalyticsReport $report)
    {
        $this->report = $report;
    }
}