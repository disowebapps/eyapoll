<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Observer;
use Illuminate\Support\Facades\Hash;

class ObserverSeeder extends Seeder
{
    public function run(): void
    {
        $observers = [
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'first_name' => 'John',
                'last_name' => 'Observer',
                'email' => 'john.observer@example.com',
                'password' => Hash::make('password123'),
                'status' => 'approved',
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'first_name' => 'Jane',
                'last_name' => 'Monitor',
                'email' => 'jane.monitor@example.com',
                'password' => Hash::make('password123'),
                'status' => 'pending',
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'first_name' => 'Mike',
                'last_name' => 'Watcher',
                'email' => 'mike.watcher@example.com',
                'password' => Hash::make('password123'),
                'status' => 'approved',
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'first_name' => 'Sarah',
                'last_name' => 'Auditor',
                'email' => 'sarah.auditor@example.com',
                'password' => Hash::make('password123'),
                'status' => 'rejected',
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'first_name' => 'David',
                'last_name' => 'Inspector',
                'email' => 'david.inspector@example.com',
                'password' => Hash::make('password123'),
                'status' => 'pending',
            ],
        ];

        foreach ($observers as $observer) {
            Observer::create($observer);
        }
    }
}