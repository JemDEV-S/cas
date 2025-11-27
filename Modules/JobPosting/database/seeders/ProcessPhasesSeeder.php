<?php

namespace Modules\JobPosting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\JobPosting\Entities\ProcessPhase;
use Illuminate\Support\Str;

class ProcessPhasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $phases = [
            [
                'code' => 'PHASE_001',
                'name' => 'Aprobación de la Convocatoria',
                'description' => 'Aprobación interna de la convocatoria por las autoridades competentes',
                'phase_number' => 1,
                'order' => 1,
                'requires_evaluation' => false,
                'is_public' => false,
                'default_duration_days' => 3,
            ],
            [
                'code' => 'PHASE_002',
                'name' => 'Publicación de la Convocatoria',
                'description' => 'Publicación oficial de la convocatoria en el portal institucional',
                'phase_number' => 2,
                'order' => 2,
                'requires_evaluation' => false,
                'is_public' => true,
                'default_duration_days' => 1,
            ],
            [
                'code' => 'PHASE_003',
                'name' => 'Registro Virtual de Postulantes',
                'description' => 'Período de inscripción y presentación de postulaciones en línea',
                'phase_number' => 3,
                'order' => 3,
                'requires_evaluation' => false,
                'is_public' => true,
                'default_duration_days' => 7,
            ],
            [
                'code' => 'PHASE_004',
                'name' => 'Publicación de Postulantes APTOS',
                'description' => 'Publicación de lista de postulantes que cumplen requisitos mínimos',
                'phase_number' => 4,
                'order' => 4,
                'requires_evaluation' => false,
                'is_public' => true,
                'default_duration_days' => 1,
            ],
            [
                'code' => 'PHASE_005',
                'name' => 'Presentación de CV Documentado',
                'description' => 'Entrega física o digital del currículum con documentación sustentaria',
                'phase_number' => 5,
                'order' => 5,
                'requires_evaluation' => false,
                'is_public' => true,
                'default_duration_days' => 2,
            ],
            [
                'code' => 'PHASE_006',
                'name' => 'Evaluación Curricular',
                'description' => 'Evaluación y calificación de currículums por el jurado evaluador',
                'phase_number' => 6,
                'order' => 6,
                'requires_evaluation' => true,
                'is_public' => false,
                'default_duration_days' => 5,
            ],
            [
                'code' => 'PHASE_007',
                'name' => 'Publicación de Resultados Curriculares',
                'description' => 'Publicación de puntajes y resultados de la evaluación curricular',
                'phase_number' => 7,
                'order' => 7,
                'requires_evaluation' => false,
                'is_public' => true,
                'default_duration_days' => 1,
            ],
            [
                'code' => 'PHASE_008',
                'name' => 'Entrevista Personal',
                'description' => 'Entrevista presencial o virtual con los postulantes clasificados',
                'phase_number' => 8,
                'order' => 8,
                'requires_evaluation' => true,
                'is_public' => false,
                'default_duration_days' => 3,
            ],
            [
                'code' => 'PHASE_009',
                'name' => 'Publicación de Resultados de Entrevista',
                'description' => 'Publicación de resultados finales y ganadores por vacante',
                'phase_number' => 9,
                'order' => 9,
                'requires_evaluation' => false,
                'is_public' => true,
                'default_duration_days' => 1,
            ],
            [
                'code' => 'PHASE_010',
                'name' => 'Suscripción de Contrato',
                'description' => 'Firma del contrato CAS con los ganadores seleccionados',
                'phase_number' => 10,
                'order' => 10,
                'requires_evaluation' => false,
                'is_public' => false,
                'default_duration_days' => 5,
            ],
            [
                'code' => 'PHASE_011',
                'name' => 'Charla de Inducción',
                'description' => 'Capacitación inicial y orientación para nuevos contratados',
                'phase_number' => 11,
                'order' => 11,
                'requires_evaluation' => false,
                'is_public' => false,
                'default_duration_days' => 1,
            ],
            [
                'code' => 'PHASE_012',
                'name' => 'Inicio de Labores',
                'description' => 'Fecha oficial de inicio de actividades del personal contratado',
                'phase_number' => 12,
                'order' => 12,
                'requires_evaluation' => false,
                'is_public' => false,
                'default_duration_days' => 1,
            ],
        ];

        foreach ($phases as $phaseData) {
            ProcessPhase::firstOrCreate(
                ['code' => $phaseData['code']],
                array_merge($phaseData, [
                    'id' => (string) Str::uuid(),
                    'is_active' => true,
                ])
            );
        }

        $this->command->info('✓ 12 fases del proceso CAS creadas exitosamente');
    }
}