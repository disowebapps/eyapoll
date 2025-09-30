<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Election\Election;

$election = Election::find(7);
echo "Election 7: {$election->title}\n";
echo "Current time: " . now() . "\n";
echo "Registration ends: {$election->candidate_register_ends}\n";
echo "Has ended: " . (now()->gt($election->candidate_register_ends) ? 'YES' : 'NO') . "\n";