<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\JobProfile\Entities\JobProfile;

echo "=== ANÁLISIS DE CARRERAS EN JOB_PROFILES ===\n\n";

// Total de perfiles
$total = JobProfile::count();
echo "Total de perfiles: {$total}\n";

// Perfiles con career_field
$withCareer = JobProfile::whereNotNull('career_field')->count();
echo "Perfiles con career_field: {$withCareer}\n";

// Perfiles sin career_field
$withoutCareer = JobProfile::whereNull('career_field')->orWhere('career_field', '')->count();
echo "Perfiles sin career_field: {$withoutCareer}\n\n";

// Carreras únicas
$careers = JobProfile::whereNotNull('career_field')
    ->where('career_field', '!=', '')
    ->pluck('career_field')
    ->unique()
    ->sort()
    ->values();

echo "Carreras únicas: {$careers->count()}\n\n";

echo "=== LISTA DE CARRERAS EN JOB_PROFILES ===\n";
foreach($careers as $i => $career) {
    $count = JobProfile::where('career_field', $career)->count();
    echo ($i+1) . ". {$career} ({$count} perfiles)\n";
}

// Guardar en archivo
file_put_contents('job_profiles_careers.json', json_encode([
    'total_perfiles' => $total,
    'con_career_field' => $withCareer,
    'sin_career_field' => $withoutCareer,
    'carreras_unicas' => $careers->count(),
    'carreras' => $careers->toArray()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n=== DATOS GUARDADOS EN: job_profiles_careers.json ===\n";
