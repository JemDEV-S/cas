<?php

namespace Modules\Evaluation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Evaluation\Entities\EvaluationCriterion;
use Modules\JobPosting\Entities\ProcessPhase;

/**
 * Seeder para criterios de evaluación automática de elegibilidad
 *
 * Crea los 8 criterios estándar para la Fase 4 (Publicación de postulantes APTOS)
 * que utiliza el AutoGraderService del sistema.
 */
class AutomaticEligibilityCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener la Fase 4 - Publicación de postulantes APTOS
        $phase4 = ProcessPhase::where('code', 'PHASE_04_ELIGIBLE_PUB')->first();

        if (!$phase4) {
            $this->command->error('❌ No se encontró la Fase 4 (PHASE_04_ELIGIBLE_PUB)');
            return;
        }

        $criteria = [
            [
                'code' => 'ELIGIBILITY_ACADEMIC',
                'name' => 'Formación Académica',
                'description' => 'Verificación automática de que el postulante cumple con el nivel educativo requerido por el perfil del cargo.',
                'order' => 1,
            ],
            [
                'code' => 'ELIGIBILITY_GENERAL_EXPERIENCE',
                'name' => 'Experiencia General',
                'description' => 'Verificación automática de que el postulante cuenta con el tiempo mínimo de experiencia laboral general requerida.',
                'order' => 2,
            ],
            [
                'code' => 'ELIGIBILITY_SPECIFIC_EXPERIENCE',
                'name' => 'Experiencia Específica',
                'description' => 'Verificación automática de que el postulante cuenta con el tiempo mínimo de experiencia en el área específica del cargo.',
                'order' => 3,
            ],
            [
                'code' => 'ELIGIBILITY_PROFESSIONAL_REGISTRY',
                'name' => 'Colegiatura Profesional',
                'description' => 'Verificación automática de que el postulante cuenta con la colegiatura o registro profesional requerido.',
                'order' => 4,
            ],
            [
                'code' => 'ELIGIBILITY_OSCE_CERTIFICATION',
                'name' => 'Certificación OSCE',
                'description' => 'Verificación automática de que el postulante cuenta con la certificación OSCE cuando es requerida.',
                'order' => 5,
            ],
            [
                'code' => 'ELIGIBILITY_DRIVER_LICENSE',
                'name' => 'Licencia de Conducir',
                'description' => 'Verificación automática de que el postulante cuenta con la licencia de conducir requerida.',
                'order' => 6,
            ],
            [
                'code' => 'ELIGIBILITY_REQUIRED_COURSES',
                'name' => 'Cursos Requeridos',
                'description' => 'Verificación automática de que el postulante cuenta con los cursos o capacitaciones requeridas para el cargo.',
                'order' => 7,
            ],
            [
                'code' => 'ELIGIBILITY_TECHNICAL_KNOWLEDGE',
                'name' => 'Conocimientos Técnicos',
                'description' => 'Verificación automática de que el postulante declara poseer los conocimientos técnicos requeridos para el cargo.',
                'order' => 8,
            ],
        ];

        foreach ($criteria as $criterion) {
            EvaluationCriterion::updateOrCreate(
                [
                    'phase_id' => $phase4->id, // process_phases no tiene uuid, usar id
                    'code' => $criterion['code'],
                ],
                [
                    'job_posting_id' => null, // Criterios globales para todas las convocatorias
                    'name' => $criterion['name'],
                    'description' => $criterion['description'],
                    'min_score' => 0,
                    'max_score' => 1,
                    'weight' => 1,
                    'order' => $criterion['order'],
                    'requires_comment' => false,
                    'requires_evidence' => false,
                    'score_type' => 'NUMERIC',
                    'evaluation_guide' => 'Este criterio es evaluado automáticamente por el sistema. Puntuación: 0 = No cumple, 1 = Cumple.',
                    'is_active' => true,
                    'is_system' => true, // Protegido del sistema
                    'metadata' => [
                        'auto_evaluated' => true,
                        'evaluator' => 'system',
                        'algorithm' => 'AutoGraderService',
                    ],
                ]
            );
        }

        $this->command->info("✅ Se crearon/actualizaron " . count($criteria) . " criterios de elegibilidad automática para la Fase 4");
    }
}
