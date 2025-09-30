<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorVoterRegistration extends Command
{
    protected $signature = 'voter:monitor-registration';
    protected $description = 'Monitor and ensure voter registration status is correct';

    public function handle()
    {
        $setting = DB::table('system_settings')
            ->where('key', 'voter_registration_enabled')
            ->first();

        $isEnabled = $setting ? $setting->value === 'true' : true;

        $this->info("Voter registration status: " . ($isEnabled ? 'ENABLED' : 'DISABLED'));
        
        return 0;
    }
}