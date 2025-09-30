<?php

namespace App\Domains\Administration\DomainEvents;

use App\Domains\Administration\Entities\SystemSetting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingUpdatedEvent
{
    use Dispatchable, SerializesModels;

    public SystemSetting $setting;

    public function __construct(SystemSetting $setting)
    {
        $this->setting = $setting;
    }
}