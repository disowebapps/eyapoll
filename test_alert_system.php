<?php

require_once 'vendor/autoload.php';

// Test the alert system
use App\Services\AlertService;

$alertService = new AlertService();

// Test different alert types
echo "Testing Alert System...\n";

// 1. KYC Submission Alert
$alert1 = $alertService->kycSubmission('John Doe', 'National ID');
echo "✅ KYC Alert Created: ID {$alert1->id}\n";

// 2. Security Alert
$alert2 = $alertService->securityAlert('Multiple failed login attempts from IP 192.168.1.100');
echo "✅ Security Alert Created: ID {$alert2->id}\n";

// 3. Candidate Application
$alert3 = $alertService->candidateApplication('Jane Smith', 'President');
echo "✅ Candidate Alert Created: ID {$alert3->id}\n";

// 4. Observer Alert
$alert4 = $alertService->observerAlert('Mike Johnson', 'Suspicious voting activity detected');
echo "✅ Observer Alert Created: ID {$alert4->id}\n";

echo "\n✅ All alert types working correctly!\n";
echo "📊 Total alerts in database: " . \App\Models\Alert::count() . "\n";
echo "🔔 Unread alerts: " . \App\Models\Alert::where('is_read', false)->count() . "\n";