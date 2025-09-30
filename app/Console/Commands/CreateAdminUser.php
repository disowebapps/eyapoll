<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ayapoll:create-admin {--email=admin@ayapoll.com} {--password=admin123} {--first_name=Admin} {--last_name=User}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user for AYApoll platform';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $firstName = $this->option('first_name');
        $lastName = $this->option('last_name');

        // Check if admin already exists
        $existingAdmin = User::where('email', $email)->first();

        if ($existingAdmin) {
            $this->error("Admin user with email {$email} already exists!");
            return 1;
        }

        // Create the admin user
        $admin = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make($password),
            'id_number_hash' => hash('sha256', 'ADMIN_' . $email . '_' . now()->timestamp), // Dummy hash for admin
            'id_salt' => \Illuminate\Support\Str::random(32), // Random salt
            'status' => 'approved',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user created successfully!");
        $this->line("Name: {$admin->full_name}");
        $this->line("Email: {$admin->email}");
        $this->line("Role: {$admin->role->value}");
        $this->line("Status: {$admin->status->value}");
        $this->newLine();
        $this->comment("You can now login to the admin panel at: /admin/login");
        $this->comment("Email: {$email}");
        $this->comment("Password: {$password}");

        return 0;
    }
}
