<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Evaluation\Entities\EvaluationCriterion;
use Modules\JobPosting\Entities\ProcessPhase;

class EvaluationCriteriaCVSeeder extends Seeder
{
    /**
     * Criterios de evaluación CV por código de puesto
     */
    private $criteriosPorPuesto = [
        'ESP I' => [
            'formacion_academica' => [
                'puntaje_minimo' => 17,
                'opciones_adicionales' => [
                    ['descripcion' => 'Egresado de Maestría', 'puntos' => 4],
                    ['descripcion' => 'Grado de Magíster', 'puntos' => 4],
                ],
                'subtotal_adicional_max' => 8
            ],
            'experiencia_especifica' => [
                'puntaje_minimo' => 18,
                'opciones_adicionales' => [
                    ['descripcion' => 'Experiencia laboral mayor a 1 año y menor a 2 años en gestión pública', 'puntos' => 3],
                    ['descripcion' => 'Experiencia laboral mayor a 2 años en gestión pública', 'puntos' => 4],
                ],
                'subtotal_adicional_max' => 7
            ]
        ],
        'PP I' => [
            'formacion_academica' => [
                'puntaje_minimo' => 17,
                'opciones_adicionales' => [
                    ['descripcion' => 'Estudio de Maestría', 'puntos' => 3],
                    ['descripcion' => 'Egresado de Maestría', 'puntos' => 5],
                ],
                'subtotal_adicional_max' => 8
            ],
            'experiencia_especifica' => [
                'puntaje_minimo' => 18,
                'opciones_adicionales' => [
                    ['descripcion' => 'Experiencia laboral mayor a 1 año y menor a 2 años en gestión pública', 'puntos' => 3],
                    ['descripcion' => 'Experiencia laboral mayor a 3 años en gestión pública', 'puntos' => 4],
                ],
                'subtotal_adicional_max' => 7
            ]
        ],
        'PP II' => [
            'formacion_academica' => [
                'puntaje_minimo' => 17,
                'opciones_adicionales' => [
                    ['descripcion' => 'Estudio de Maestría', 'puntos' => 3],
                    ['descripcion' => 'Egresado de Maestría', 'puntos' => 5],
                ],
                'subtotal_adicional_max' => 8
            ],
            'experiencia_especifica' => [
                'puntaje_minimo' => 18,
                'opciones_adicionales' => [
                    ['descripcion' => 'Experiencia laboral mayor a 1 año y menor a 2 años en gestión pública', 'puntos' => 3],
                    ['descripcion' => 'Experiencia laboral mayor a 2 años en gestión pública', 'puntos' => 4],
                ],
                'subtotal_adicional_max' => 7
            ]
        ],
        'PP III' => [
            'formacion_academica' => [
                'puntaje_minimo' => 17,
                'opciones_adicionales' => [
                    ['descripcion' => 'Estudio de Maestría', 'puntos' => 3],
                    ['descripcion' => 'Egresado de Maestría', 'puntos' => 5],
                ],
                'subtotal_adicional_max' => 8
            ],
            'experiencia_especifica' => [
                'puntaje_minimo' => 18,
                'opciones_adicionales' => [
                    ['descripcion' => 'Experiencia laboral mayor a 6 meses y menor a 1 año en gestión pública', 'puntos' => 3],
                    ['descripcion' => 'Experiencia laboral mayor a 1 año en gestión pública', 'puntos' => 4],
                ],
                'subtotal_adicional_max' => 7
            ]
        ],
        'AA I' => [
            'formacion_academica' => [
                'puntaje_minimo' => 17,
                'opciones_adicionales' => [
                    ['descripcion' => 'Título Profesional', 'puntos' => 4],
                    ['descripcion' => 'Estudio de Maestría', 'puntos' => 4],
                ],
                'subtotal_adicional_max' => 8
            ],
            'experiencia_especifica' => [
                'puntaje_minimo' => 18,
                'opciones_adicionales' => [
                    ['descripcion' => 'Experiencia laboral mayor a 6 meses y menor a 1 año en gestión pública', 'puntos' => 3],
                    ['descripcion' => 'Experiencia laboral mayor a 1 año en gestión pública', 'puntos' => 4],
                ],
                'subtotal_adicional_max' => 7
            ]
        ],
        'TEC I' => [
            'formacion_academica' => [
                'puntaje_minimo' => 17,
                'opciones_adicionales' => [
                    ['descripcion' => 'Grado de bachiller o Título Técnico', 'puntos' => 4],
                    ['descripcion' => 'Título Universitario', 'puntos' => 4],
                ],
                'subtotal_adicional_max' => 8
            ],
            'experiencia_especifica' => [
                'puntaje_minimo' => 18,
                'opciones_adicionales' => [
                    ['descripcion' => 'Experiencia laboral general mayor a 1 años y menor a 2 años', 'puntos' => 3],
                    ['descripcion' => 'Experiencia laboral general mayor a 2 años', 'puntos' => 4],
                ],
                'subtotal_adicional_max' => 7
            ]
        ],
        'TEC II' => [
            'formacion_academica' => [
                'puntaje_minimo' => 17,
                'opciones_adicionales' => [
                    ['descripcion' => 'Grado de bachiller o Título Técnico', 'puntos' => 8],
                ],
                'subtotal_adicional_max' => 8
            ],
            'experiencia_especifica' => [
                'puntaje_minimo' => 18,
                'opciones_adicionales' => [
                    ['descripcion' => 'Experiencia laboral general mayor a 6 meses y menor a 1 año', 'puntos' => 3],
                    ['descripcion' => 'Experiencia laboral general mayor a 1 año', 'puntos' => 4],
                ],
                'subtotal_adicional_max' => 7
            ]
        ],
        'AAI' => [
            'formacion_academica' => [
                'puntaje_minimo' => 17,
                'opciones_adicionales' => [
                    ['descripcion' => 'Egresado Universitario o Técnico', 'puntos' => 4],
                    ['descripcion' => 'Grado de bachiller', 'puntos' => 4],
                ],
                'subtotal_adicional_max' => 8
            ],
            'experiencia_especifica' => [
                'puntaje_minimo' => 18,
                'opciones_adicionales' => [
                    ['descripcion' => 'Experiencia laboral general mayor a 6 meses y menor a 1 año', 'puntos' => 7],
                ],
                'subtotal_adicional_max' => 7
            ]
        ],
        'AAII' => [
            'formacion_academica' => [
                'puntaje_minimo' => 17,
                'opciones_adicionales' => [
                    ['descripcion' => 'Egresado Universitario o Técnico', 'puntos' => 8],
                ],
                'subtotal_adicional_max' => 8
            ],
            'experiencia_especifica' => [
                'puntaje_minimo' => 18,
                'opciones_adicionales' => [
                    ['descripcion' => 'Experiencia laboral general mayor a 6 meses y menor a 1 año', 'puntos' => 4],
                    ['descripcion' => 'Experiencia laboral general mayor a 1 año', 'puntos' => 3],
                ],
                'subtotal_adicional_max' => 7
            ]
        ],
        'AAIII' => [
            'formacion_academica' => [
                'puntaje_minimo' => 17,
                'opciones_adicionales' => [
                    ['descripcion' => 'Estudios Universitarios o Técnicos', 'puntos' => 7],
                ],
                'subtotal_adicional_max' => 7
            ],
            'experiencia_especifica' => [
                'puntaje_minimo' => 18,
                'opciones_adicionales' => [
                    ['descripcion' => 'Experiencia laboral general mayor a 6 meses y menor a 1 año', 'puntos' => 8],
                ],
                'subtotal_adicional_max' => 8
            ]
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener la fase de Evaluación Curricular
        $cvPhase = ProcessPhase::where('code', 'PHASE_06_CV_EVALUATION')->first();

        if (!$cvPhase) {
            $this->command->error('No se encontró la fase de Evaluación Curricular. Asegúrate de ejecutar primero el seeder de fases.');
            return;
        }

        $this->command->info('Iniciando seed de criterios de evaluación CV...');
        $this->command->info("Fase encontrada: {$cvPhase->name} (ID: {$cvPhase->id})");

        foreach ($this->criteriosPorPuesto as $positionCode => $criterios) {
            $this->crearCriteriosParaPuesto($cvPhase->id, $positionCode, $criterios);
        }

        $this->command->info('✅ Criterios de evaluación CV creados exitosamente');
    }

    /**
     * Crear criterios para un puesto específico
     */
    private function crearCriteriosParaPuesto($phaseUuid, $positionCode, $criterios)
    {
        $this->command->line("Creando criterios para puesto: {$positionCode}");

        // 1. Formación Académica - Requisitos Mínimos
        EvaluationCriterion::updateOrCreate(
            [
                'code' => "CV_FA_MIN_{$positionCode}",
            ],
            [
                'phase_id' => $phaseUuid,
                'job_posting_id' => null, // General para todos
                'name' => 'Formación Académica - Requisitos Mínimos',
                'description' => 'Evaluación de formación académica según requisitos mínimos del puesto',
                'min_score' => 0,
                'max_score' => $criterios['formacion_academica']['puntaje_minimo'],
                'weight' => 1,
                'order' => 1,
                'requires_comment' => false,
                'requires_evidence' => true,
                'score_type' => 'NUMERIC',
                'is_active' => true,
                'is_system' => true,
                'metadata' => [
                    'position_code' => $positionCode,
                    'tipo_criterio' => 'formacion_academica_minimo',
                    'puntaje_minimo_aprobatorio' => $criterios['formacion_academica']['puntaje_minimo']
                ]
            ]
        );

        // 2. Formación Académica - Requisitos Adicionales
        $opcionesFormacion = array_map(function($opcion, $index) {
            return [
                'id' => $index + 1,
                'descripcion' => $opcion['descripcion'],
                'puntos' => $opcion['puntos']
            ];
        }, $criterios['formacion_academica']['opciones_adicionales'], array_keys($criterios['formacion_academica']['opciones_adicionales']));

        EvaluationCriterion::updateOrCreate(
            [
                'code' => "CV_FA_ADIC_{$positionCode}",
            ],
            [
                'phase_id' => $phaseUuid,
                'job_posting_id' => null,
                'name' => 'Formación Académica - Requisitos Adicionales',
                'description' => 'Evaluación de formación académica adicional',
                'min_score' => 0,
                'max_score' => $criterios['formacion_academica']['subtotal_adicional_max'],
                'weight' => 1,
                'order' => 2,
                'requires_comment' => false,
                'requires_evidence' => true,
                'score_type' => 'NUMERIC',
                'score_scales' => $opcionesFormacion,
                'evaluation_guide' => 'Seleccione las opciones que cumple el postulante. El puntaje máximo es ' . $criterios['formacion_academica']['subtotal_adicional_max'],
                'is_active' => true,
                'is_system' => true,
                'metadata' => [
                    'position_code' => $positionCode,
                    'tipo_criterio' => 'formacion_academica_adicional',
                    'permite_multiple' => true
                ]
            ]
        );

        // 3. Experiencia Específica - Requisitos Mínimos
        EvaluationCriterion::updateOrCreate(
            [
                'code' => "CV_EXP_MIN_{$positionCode}",
            ],
            [
                'phase_id' => $phaseUuid,
                'job_posting_id' => null,
                'name' => 'Experiencia Específica - Requisitos Mínimos',
                'description' => 'Evaluación de experiencia específica según requisitos mínimos del puesto',
                'min_score' => 0,
                'max_score' => $criterios['experiencia_especifica']['puntaje_minimo'],
                'weight' => 1,
                'order' => 3,
                'requires_comment' => false,
                'requires_evidence' => true,
                'score_type' => 'NUMERIC',
                'is_active' => true,
                'is_system' => true,
                'metadata' => [
                    'position_code' => $positionCode,
                    'tipo_criterio' => 'experiencia_especifica_minimo',
                    'puntaje_minimo_aprobatorio' => $criterios['experiencia_especifica']['puntaje_minimo']
                ]
            ]
        );

        // 4. Experiencia Específica - Requisitos Adicionales
        $opcionesExperiencia = array_map(function($opcion, $index) {
            return [
                'id' => $index + 1,
                'descripcion' => $opcion['descripcion'],
                'puntos' => $opcion['puntos']
            ];
        }, $criterios['experiencia_especifica']['opciones_adicionales'], array_keys($criterios['experiencia_especifica']['opciones_adicionales']));

        EvaluationCriterion::updateOrCreate(
            [
                'code' => "CV_EXP_ADIC_{$positionCode}",
            ],
            [
                'phase_id' => $phaseUuid,
                'job_posting_id' => null,
                'name' => 'Experiencia Específica - Requisitos Adicionales',
                'description' => 'Evaluación de experiencia adicional',
                'min_score' => 0,
                'max_score' => $criterios['experiencia_especifica']['subtotal_adicional_max'],
                'weight' => 1,
                'order' => 4,
                'requires_comment' => false,
                'requires_evidence' => true,
                'score_type' => 'NUMERIC',
                'score_scales' => $opcionesExperiencia,
                'evaluation_guide' => 'Seleccione las opciones que cumple el postulante. El puntaje máximo es ' . $criterios['experiencia_especifica']['subtotal_adicional_max'],
                'is_active' => true,
                'is_system' => true,
                'metadata' => [
                    'position_code' => $positionCode,
                    'tipo_criterio' => 'experiencia_especifica_adicional',
                    'permite_multiple' => true
                ]
            ]
        );
    }
}
