<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Election\Election;

$elections = Election::where('phase', '!=', 'completed')
    ->orderBy('candidate_register_starts')
    ->get(['id', 'title', 'phase', 'candidate_register_starts', 'candidate_register_ends']);

foreach($elections as $e) {
    echo "Election {$e->id}: {$e->title}\n";
    echo "Phase: " . $e->phase->value . "\n";
    echo "Candidate Registration: {$e->candidate_register_starts} to {$e->candidate_register_ends}\n";
    echo "---\n";
}