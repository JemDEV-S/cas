<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$year = date('Y');

echo "=== Testing Code Generation Logic ===" . PHP_EOL . PHP_EOL;

// Simular la consulta actual
$lastProfile = DB::table('job_profiles')
    ->whereNull('job_posting_id')
    ->whereYear('created_at', $year)
    ->where('code', 'like', 'PROF-' . $year . '-%')
    ->orderBy('code', 'desc')
    ->first();

echo "Last profile found: " . PHP_EOL;
if ($lastProfile) {
    echo "  Code: " . $lastProfile->code . PHP_EOL;
    echo "  Created: " . $lastProfile->created_at . PHP_EOL;

    $parts = explode('-', $lastProfile->code);
    $lastNumber = (int) end($parts);
    $nextNumber = $lastNumber + 1;

    echo "  Last number: " . $lastNumber . PHP_EOL;
    echo "  Next number: " . $nextNumber . PHP_EOL;
    echo "  Next code: PROF-" . $year . "-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT) . PHP_EOL;
} else {
    echo "  NULL (no profiles found)" . PHP_EOL;
    echo "  Next code: PROF-" . $year . "-001" . PHP_EOL;
}

echo PHP_EOL . "=== All Profiles ===" . PHP_EOL;
$allProfiles = DB::table('job_profiles')
    ->whereNull('job_posting_id')
    ->whereYear('created_at', $year)
    ->orderBy('created_at', 'desc')
    ->get(['code', 'created_at']);

foreach ($allProfiles as $profile) {
    echo "  " . $profile->code . " - " . $profile->created_at . PHP_EOL;
}
