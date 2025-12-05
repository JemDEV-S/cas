<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\JobProfile\Services\JobProfileService;
use Illuminate\Support\Facades\Auth;

// Simular autenticación (ajusta el ID según tu base de datos)
Auth::loginUsingId(1);

$service = app(JobProfileService::class);

echo "=== Attempting to create a Job Profile ===" . PHP_EOL . PHP_EOL;

$data = [
    'title' => 'Test Profile',
    'organizational_unit_id' => '9d99c5fe-c370-403f-acce-90dda5d21c64', // Ajusta según tu BD
    'contract_type' => 'CAS',
    'position_code_id' => '9da4bb44-a25d-4464-8b12-2cfb9b5ff77d', // Ajusta según tu BD
];

try {
    $profile = $service->create($data, [], []);
    echo "✓ Profile created successfully!" . PHP_EOL;
    echo "  Code: " . $profile->code . PHP_EOL;
    echo "  ID: " . $profile->id . PHP_EOL;
} catch (\Exception $e) {
    echo "✗ Error creating profile:" . PHP_EOL;
    echo "  Message: " . $e->getMessage() . PHP_EOL;
    echo "  Class: " . get_class($e) . PHP_EOL;
    echo "  Code: " . $e->getCode() . PHP_EOL;

    if ($e instanceof \Illuminate\Database\QueryException) {
        echo "  Error Info: " . json_encode($e->errorInfo) . PHP_EOL;
    }

    echo PHP_EOL . "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
