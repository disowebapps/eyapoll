<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Election\Election;
use App\Models\Election\Position;

class EyaElectionPositionsSeeder extends Seeder
{
    public function run()
    {
        $election = Election::where('title', 'Eya National Executive Election 2025')->first();
        
        if (!$election) {
            $this->command->error('Election "Eya National Executive Election 2025" not found');
            return;
        }

        $positions = [
            [
                'title' => 'President',
                'description' => 'Chief Executive Officer of the organization, responsible for overall leadership and strategic direction.',
                'max_selections' => 1,
            ],
            [
                'title' => 'Vice President',
                'description' => 'Deputy to the President, assists in executive functions and represents the organization.',
                'max_selections' => 1,
            ],
            [
                'title' => 'Secretary General',
                'description' => 'Responsible for administrative functions, record keeping, and organizational coordination.',
                'max_selections' => 1,
            ],
        ];

        foreach ($positions as $positionData) {
            Position::create([
                'election_id' => $election->id,
                'title' => $positionData['title'],
                'description' => $positionData['description'],
                'max_selections' => $positionData['max_selections'],
            ]);
        }

        $this->command->info('Successfully added 3 positions to Eya National Executive Election 2025');
    }
}