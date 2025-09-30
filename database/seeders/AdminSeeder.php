<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Admin::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'email' => 'admin@ayapoll.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'first_name' => 'Eleco',
            'last_name' => 'Chair',
            'status' => 'approved',
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);
    }
}
