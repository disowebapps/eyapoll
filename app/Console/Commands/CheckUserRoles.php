<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserRoles extends Command
{
    protected $signature = 'users:check-roles';
    protected $description = 'Check current user roles';

    public function handle()
    {
        $users = User::select('email', 'role')->limit(20)->get();
        
        $this->info('Current User Roles:');
        foreach ($users as $user) {
            $this->line("{$user->email} -> {$user->role->value}");
        }
        
        return 0;
    }
}