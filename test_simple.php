<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'cyfer313@gmail.com')->first();
$election = \App\Models\Election\Election::find(7);
$now = now();

echo "Current time: " . $now . "\n";
echo "Register ends: " . $election->candidate_register_ends . "\n";
echo "Is now > register_ends? " . ($now->gt($election->candidate_register_ends) ? 'YES' : 'NO') . "\n";

// Test the exact condition from the view
$applicationEnded = $election->candidate_register_ends && $now->gt($election->candidate_register_ends);
echo "applicationEnded variable: " . ($applicationEnded ? 'TRUE' : 'FALSE') . "\n";