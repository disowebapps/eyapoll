<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Candidate\Candidate;
use App\Models\Election\Election;
use Illuminate\Support\Facades\Gate;

$user = User::where('email', 'cyfer313@gmail.com')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}

echo "User: {$user->email} (ID: {$user->id})\n";
echo "Status: {$user->status->value}\n";
echo "Role: {$user->role->value}\n";

$hasApplied = $user->hasActiveCandidateApplications();
echo "Has applied: " . ($hasApplied ? 'YES' : 'NO') . "\n";

$elections = Election::where('phase', '!=', 'completed')->get();
foreach($elections as $election) {
    echo "\nElection {$election->id}: {$election->title}\n";
    echo "Phase: {$election->phase->value}\n";
    
    $applicationEnded = $election->candidate_register_ends && now()->gt($election->candidate_register_ends);
    echo "Application ended: " . ($applicationEnded ? 'YES' : 'NO') . "\n";
    
    auth()->login($user);
    $canApply = Gate::allows('apply', $election);
    echo "Can apply: " . ($canApply ? 'YES' : 'NO') . "\n";
    
    if ($hasApplied) {
        echo "Should see: View Application\n";
    } elseif ($applicationEnded || !$canApply) {
        echo "Should see: Application ended\n";
    } else {
        echo "Should see: Apply as Candidate\n";
    }
}