<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExecutiveSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['position' => 'President', 'order' => 1],
            ['position' => 'Vice President', 'order' => 2],
            ['position' => 'Secretary', 'order' => 3],
            ['position' => 'Assistant Secretary', 'order' => 4],
            ['position' => 'Treasurer', 'order' => 5],
            ['position' => 'Financial Secretary', 'order' => 6],
            ['position' => 'Assistant Financial Secretary', 'order' => 7],
            ['position' => 'Publicity Secretary', 'order' => 8],
            ['position' => 'Director of Social and Mobilization', 'order' => 9],
        ];

        $users = User::whereIn('status', ['approved', 'accredited'])
                    ->whereNotNull('first_name')
                    ->whereNotNull('last_name')
                    ->limit(count($positions))
                    ->get();

        if ($users->count() < count($positions)) {
            $this->command->warn("Only {$users->count()} users available, but {" . count($positions) . "} positions needed.");
        }

        foreach ($positions as $index => $positionData) {
            if (isset($users[$index])) {
                $user = $users[$index];
                $user->update([
                    'is_executive' => true,
                    'current_position' => $positionData['position'],
                    'executive_order' => $positionData['order'],
                    'term_start' => now()->startOfYear(),
                    'term_end' => now()->endOfYear(),
                ]);

                $this->command->info("Assigned {$user->full_name} as {$positionData['position']}");
            }
        }
    }
}