<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ResetAdminPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ayapoll:reset-admin-passwords {--password=Admin1010} {--role=admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset passwords for all admin/observer users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $password = $this->option('password');
        $role = $this->option('role');

        // Validate role
        if (!in_array($role, ['admin', 'observer', 'all'])) {
            $this->error("Invalid role. Must be 'admin', 'observer', or 'all'.");
            return 1;
        }

        // Confirm action
        if (!$this->confirm("This will reset passwords for ALL {$role} users to '{$password}'. Continue?", true)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            DB::beginTransaction();

            // Get users based on role
            if ($role === 'admin') {
                $users = \App\Models\Admin::all();
            } elseif ($role === 'observer') {
                $users = \App\Models\Observer::all();
            } else { // 'all'
                $admins = \App\Models\Admin::all();
                $observers = \App\Models\Observer::all();
                $users = $admins->merge($observers);
            }

            if ($users->isEmpty()) {
                $this->info("No {$role} users found.");
                return 0;
            }

            $count = 0;
            foreach ($users as $user) {
                $user->update([
                    'password' => Hash::make($password)
                ]);

                // Skip audit logging for console commands to avoid IP hash issues
                // TODO: Fix audit logging for console commands

                $count++;
            }

            DB::commit();

            $this->info("Successfully reset passwords for {$count} {$role} user(s).");
            $this->newLine();
            $this->comment("New password: {$password}");
            $this->comment("Users affected: {$count}");

            // List affected users
            $this->newLine();
            $this->info('Affected users:');
            foreach ($users as $user) {
                $roleName = $role === 'all' ? (isset($user->role) ? $user->role->value : 'admin') : $role;
                $this->line("  - {$user->full_name} ({$user->email}) - {$roleName}");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to reset passwords: ' . $e->getMessage());
            return 1;
        }
    }
}
