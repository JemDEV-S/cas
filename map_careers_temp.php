<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$jobProfileId = '5d9f6163-c0b2-45e9-9366-067241f53ba8';

$contabilidad = DB::table('academic_careers')->where('code', 'CAR_CONTABILIDAD')->first();
$economia = DB::table('academic_careers')->where('code', 'CAR_ECONOMIA')->first();

DB::table('job_profile_careers')->insert([
    'id' => Illuminate\Support\Str::uuid()->toString(),
    'job_profile_id' => $jobProfileId,
    'career_id' => $contabilidad->id,
    'is_primary' => false,
    'mapping_source' => 'MANUAL',
    'mapped_from_text' => 'CONTABILIDAD',
    'confidence_score' => 100,
    'created_at' => now(),
    'updated_at' => now(),
]);
echo "Agregado: Contabilidad\n";

DB::table('job_profile_careers')->insert([
    'id' => Illuminate\Support\Str::uuid()->toString(),
    'job_profile_id' => $jobProfileId,
    'career_id' => $economia->id,
    'is_primary' => false,
    'mapping_source' => 'MANUAL',
    'mapped_from_text' => 'ECONOMIA',
    'confidence_score' => 100,
    'created_at' => now(),
    'updated_at' => now(),
]);
echo "Agregado: Economia\n";

echo "\n=== CARRERAS MAPEADAS AHORA ===\n";
$mapped = DB::table('job_profile_careers')
    ->join('academic_careers', 'job_profile_careers.career_id', '=', 'academic_careers.id')
    ->where('job_profile_careers.job_profile_id', $jobProfileId)
    ->select('academic_careers.name')
    ->get();

foreach ($mapped as $c) {
    echo "- {$c->name}\n";
}
