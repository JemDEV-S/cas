<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\JobProfile\Entities\JobProfile;
use Modules\JobProfile\Entities\JobProfileCareer;
use Modules\Application\Entities\AcademicCareer;

class MapRemainingProfilesCommand extends Command
{
    protected $signature = 'job-profiles:map-remaining';
    protected $description = 'Mapea manualmente los perfiles restantes con patrones conocidos';

    public function handle()
    {
        $this->info('🔧 Mapeando perfiles restantes con patrones conocidos...');
        $this->newLine();

        $mapped = 0;

        // 1. Combinaciones Administración + Contabilidad + Economía
        $adminContaEco = JobProfile::whereNotNull('career_field')
            ->whereDoesntHave('careers')
            ->where(function($q) {
                $q->where('career_field', 'like', '%administra%')
                  ->where('career_field', 'like', '%contabilidad%')
                  ->where(function($q2) {
                      $q2->where('career_field', 'like', '%econom%')
                         ->orWhere('career_field', 'like', '%turismo%');
                  });
            })
            ->get();

        foreach ($adminContaEco as $profile) {
            $this->mapMultipleCareers($profile, ['Administración', 'Contabilidad', 'Economía']);
            $mapped++;
        }

        // 2. Ing. Ambiental + Biología
        $ambBio = JobProfile::whereNotNull('career_field')
            ->whereDoesntHave('careers')
            ->where('career_field', 'like', '%ambiental%')
            ->where('career_field', 'like', '%biolog%')
            ->get();

        foreach ($ambBio as $profile) {
            $this->mapMultipleCareers($profile, ['Ingeniería Ambiental', 'Biología']);
            $mapped++;
        }

        // 3. Ing. Sistemas + Informática + Computación
        $sistemas = JobProfile::whereNotNull('career_field')
            ->whereDoesntHave('careers')
            ->where(function($q) {
                $q->where('career_field', 'like', '%sistem%')
                  ->orWhere('career_field', 'like', '%informatica%')
                  ->orWhere('career_field', 'like', '%computacion%');
            })
            ->get();

        foreach ($sistemas as $profile) {
            $this->mapMultipleCareers($profile, ['Ingeniería de Sistemas', 'Ingeniería Informática', 'Computación e Informática']);
            $mapped++;
        }

        // 4. Solo Administración + Contabilidad (sin Economía)
        $adminConta = JobProfile::whereNotNull('career_field')
            ->whereDoesntHave('careers')
            ->where('career_field', 'like', '%administra%')
            ->where('career_field', 'like', '%contabilidad%')
            ->where('career_field', 'not like', '%econom%')
            ->get();

        foreach ($adminConta as $profile) {
            $this->mapMultipleCareers($profile, ['Administración', 'Contabilidad']);
            $mapped++;
        }

        $this->newLine();
        $this->info("✅ Perfiles mapeados: {$mapped}");

        // Mostrar los que quedaron sin mapear
        $remaining = JobProfile::whereNotNull('career_field')
            ->where('career_field', '!=', '')
            ->whereDoesntHave('careers')
            ->count();

        $this->info("⚠️  Perfiles restantes sin mapeo: {$remaining}");

        if ($remaining > 0) {
            $this->newLine();
            $this->warn('Los siguientes perfiles requieren revisión manual:');
            $remainingProfiles = JobProfile::whereNotNull('career_field')
                ->where('career_field', '!=', '')
                ->whereDoesntHave('careers')
                ->get(['code', 'career_field']);

            foreach ($remainingProfiles as $p) {
                $this->line("  [{$p->code}] {$p->career_field}");
            }
        }

        return 0;
    }

    protected function mapMultipleCareers(JobProfile $profile, array $careerNames): void
    {
        foreach ($careerNames as $careerName) {
            $career = AcademicCareer::where('name', $careerName)->first();

            if (!$career) {
                continue;
            }

            JobProfileCareer::updateOrCreate(
                [
                    'job_profile_id' => $profile->id,
                    'career_id' => $career->id,
                ],
                [
                    'is_primary' => false,
                    'mapping_source' => 'MANUAL',
                    'mapped_from_text' => $profile->career_field,
                    'confidence_score' => 100.0,
                ]
            );
        }

        $this->line("✓ [{$profile->code}] {$profile->career_field}");
    }
}
