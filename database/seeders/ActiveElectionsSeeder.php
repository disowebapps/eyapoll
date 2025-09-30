<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Candidate\Candidate;
use App\Enums\Election\ElectionStatus;
use Illuminate\Support\Str;

class ActiveElectionsSeeder extends Seeder
{
    public function run(): void
    {
        // Delete existing active elections first
        Election::where('status', ElectionStatus::ACTIVE)
            ->where('title', '!=', 'EYA Benin City Chapter Election 2024')
            ->delete();
            
        $elections = [
            [
                'title' => 'National Youth Council Election 2024',
                'positions' => [
                    ['name' => 'President', 'candidates' => 3],
                    ['name' => 'Vice President', 'candidates' => 2],
                ]
            ],
            [
                'title' => 'Regional Student Union Election',
                'positions' => [
                    ['name' => 'Chairman', 'candidates' => 2],
                    ['name' => 'Secretary', 'candidates' => 1],
                ]
            ]
        ];

        foreach ($elections as $electionData) {
            $election = Election::create([
                'uuid' => Str::uuid(),
                'title' => $electionData['title'],
                'description' => 'Active election for ' . $electionData['title'],
                'type' => 'general',
                'status' => ElectionStatus::ACTIVE,
                'starts_at' => now()->subHours(2),
                'ends_at' => now()->addDays(7),
                'created_by' => 1,
            ]);

            foreach ($electionData['positions'] as $positionData) {
                $position = Position::create([
                    'election_id' => $election->id,
                    'title' => $positionData['name'],
                    'description' => $positionData['name'] . ' position',
                    'max_selections' => 1,
                    'order_index' => 1,
                    'is_active' => true,
                ]);

                $usedUsers = [];
                for ($i = 1; $i <= $positionData['candidates']; $i++) {
                    do {
                        $userId = rand(2, 20);
                    } while (in_array($userId, $usedUsers));
                    $usedUsers[] = $userId;
                    
                    Candidate::create([
                        'user_id' => $userId,
                        'election_id' => $election->id,
                        'position_id' => $position->id,
                        'manifesto' => 'Sample manifesto for candidate ' . $i,
                        'application_fee' => 0,
                        'payment_status' => 'paid',
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => 1,
                    ]);
                }
            }
        }
    }
}