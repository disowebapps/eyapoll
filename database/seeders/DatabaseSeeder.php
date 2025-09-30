<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Always seed notification templates (new channel-specific structure)
        $this->call([
            EmailTemplateSeeder::class,
            SmsTemplateSeeder::class,
            InAppTemplateSeeder::class,
        ]);

        // Only seed test data if enabled
        if (config('ayapoll.development.seed_test_data', false)) {
            $this->call([
                AyapollSeeder::class,
            ]);
        }
    }
}
