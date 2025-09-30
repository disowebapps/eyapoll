<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'cyfer313@gmail.com')->first();
$election = \App\Models\Election\Election::find(7);

echo "User ID: " . $user->id . "\n";
echo "Election ID: " . $election->id . "\n";
echo "User approved: " . ($user->isApproved() ? 'Yes' : 'No') . "\n";

$hasAppliedToThisElection = \App\Models\Candidate\Candidate::where('user_id', $user->id)
    ->where('election_id', $election->id)
    ->whereIn('status', ['pending', 'approved'])
    ->exists();

echo "Has applied to this election: " . ($hasAppliedToThisElection ? 'Yes' : 'No') . "\n";

$now = now();
echo "Now: " . $now . "\n";
echo "Register starts: " . $election->candidate_register_starts . "\n";
echo "Register ends: " . $election->candidate_register_ends . "\n";

$applicationNotStarted = $election->candidate_register_starts && $now->lt($election->candidate_register_starts);
$applicationEnded = $election->candidate_register_ends && $now->gt($election->candidate_register_ends);

echo "Application not started: " . ($applicationNotStarted ? 'Yes' : 'No') . "\n";
echo "Application ended: " . ($applicationEnded ? 'Yes' : 'No') . "\n";
echo "Election can accept applications: " . ($election->canAcceptCandidateApplications() ? 'Yes' : 'No') . "\n";