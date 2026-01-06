<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Application\Entities\AcademicCareer;
use Modules\Application\Entities\AcademicCareerSynonym;
use Illuminate\Support\Str;

class AddCommonSynonymsCommand extends Command
{
    protected $signature = 'catalog:add-common-synonyms';
    protected $description = 'Agrega sinónimos comunes encontrados en job profiles';

    public function handle()
    {
        $this->info('📝 Agregando sinónimos comunes...');

        $synonyms = [
            // Sistemas e Informática
            'Ingeniería de Sistemas' => [
                'ING. SISTEMAS',
                'ING SISTEMAS',
                'INGENIERIA SISTEMAS',
                'SISTEMAS',
            ],
            'Ingeniería Informática' => [
                'ING. INFORMATICA',
                'ING INFORMATICA',
                'INGENIERIA INFORMATICA',
                'INFORMATICA',
            ],
            'Computación e Informática' => [
                'COMPUTACION',
                'INGENIERIA DE COMPUTACION',
            ],

            // Administración y Contabilidad
            'Administración' => [
                'ADMINISTRACION',
                'CIENCIAS ADMINISTRATIVAS',
                'ADMINISTRACION DE EMPRESAS',
            ],
            'Contabilidad' => [
                'CONTABILIDAD',
                'CIENCIAS CONTABLES',
            ],
            'Economía' => [
                'ECONOMIA',
                'CIENCIAS ECONOMICAS',
            ],

            // Ingeniería Ambiental
            'Ingeniería Ambiental' => [
                'ING. AMBIENTAL',
                'ING AMBIENTAL',
                'INGENIERIA AMBIENTAL',
            ],

            // Derecho
            'Derecho' => [
                'DERECHO',
                'CIENCIAS JURIDICAS',
            ],

            // Turismo
            'Turismo' => [
                'TURISMO',
                'TURISMO Y HOTELERIA',
            ],

            // Telecomunicaciones
            'Ingeniería Electrónica' => [
                'TELECOMUNICACIONES',
                'INGENIERIA EN TELECOMUNICACIONES',
            ],

            // Trabajo Social
            'Trabajo Social' => [
                'TRABAJO SOCIAL',
                'SOCIAL',
            ],
        ];

        $added = 0;
        $skipped = 0;

        foreach ($synonyms as $careerName => $careerSynonyms) {
            $career = AcademicCareer::where('name', $careerName)->first();

            if (!$career) {
                $this->warn("Carrera no encontrada: {$careerName}");
                continue;
            }

            foreach ($careerSynonyms as $synonym) {
                $exists = AcademicCareerSynonym::where('synonym', $synonym)->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                AcademicCareerSynonym::create([
                    'id' => (string) Str::uuid(),
                    'career_id' => $career->id,
                    'synonym' => $synonym,
                    'source' => 'MANUAL',
                    'is_approved' => true,
                ]);

                $added++;
            }
        }

        $this->newLine();
        $this->info("✅ Sinónimos agregados: {$added}");
        $this->info("⏭️  Sinónimos ya existentes: {$skipped}");

        return 0;
    }
}
