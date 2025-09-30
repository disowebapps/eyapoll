<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'cyfer313@gmail.com')->first();
$election = \App\Models\Election\Election::find(7);

echo "User role: " . $user->role->value . "\n";
echo "Can apply as candidate: " . ($user->role->canApplyAsCandidate() ? 'Yes' : 'No') . "\n";
echo "Is approved: " . ($user->isApproved() ? 'Yes' : 'No') . "\n";
echo "Application ended: " . ($election->candidate_register_ends && now()->gt($election->candidate_register_ends) ? 'Yes' : 'No') . "\n";

$userCanApply = $user->role->canApplyAsCandidate() && $user->isApproved();
echo "user_can_apply: " . ($userCanApply ? 'Yes' : 'No') . "\n";