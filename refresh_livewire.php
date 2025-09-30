<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Clear any cached data
\Illuminate\Support\Facades\Cache::flush();

echo "Cache flushed\n";