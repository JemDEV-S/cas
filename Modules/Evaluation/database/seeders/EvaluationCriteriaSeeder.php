<?php

namespace Modules\Evaluation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Evaluation\Entities\EvaluationCriterion;
use Modules\JobPosting\Entities\ProcessPhase;

class EvaluationCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener las fases que requieren evaluación
        $interviewPhase = ProcessPhase::where('code', 'PHASE_08_INTERVIEW')->first();

        if (!$interviewPhase) {
            $this->command->warn('⚠️  Fase de entrevista no encontrada. Ejecuta primero ProcessPhasesSeeder.');
            return;
        }

        // Criterios para Entrevista Personal (PHASE_08) - Actualizado según normativa municipal
        $interviewCriteria = [
            [
                'code' => 'INT_KNOWLEDGE',
                'name' => 'Dominio y conocimiento de las funciones del puesto',
                'description' => 'Destreza y conocimiento teórico-práctico sobre las funciones del puesto al que postula',
                'min_score' => 0,
                'max_score' => 12.5,
                'weight' => 0.25,
                'order' => 1,
                'requires_comment' => false,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    12.5 pts: Demuestra dominio excepcional y conocimiento profundo de las funciones
                    10.0 pts: Conocimiento sólido y preciso de las funciones del puesto
                    7.5 pts: Conocimiento adecuado de las funciones principales
                    5.0 pts: Conocimiento básico de las funciones
                    2.5 pts: Conocimiento insuficiente
                    0 pts: No demuestra conocimientos requeridos
                ',
            ],
            [
                'code' => 'INT_ANALYSIS',
                'name' => 'Grado de Análisis',
                'description' => 'Capacidad para comprender situaciones y resolver problemas. Poseer la habilidad para realizar un análisis lógico, la capacidad de identificar problemas, reconocer información significativa',
                'min_score' => 0,
                'max_score' => 12.5,
                'weight' => 0.25,
                'order' => 2,
                'requires_comment' => false,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    12.5 pts: Análisis excepcional, identifica problemas complejos y propone soluciones innovadoras
                    10.0 pts: Buen análisis lógico y propuestas viables
                    7.5 pts: Análisis adecuado de situaciones
                    5.0 pts: Análisis básico con algunas dificultades
                    2.5 pts: Dificultad para analizar situaciones
                    0 pts: No puede realizar análisis de problemas
                ',
            ],
            [
                'code' => 'INT_ETHICS',
                'name' => 'Ética y Actitud',
                'description' => 'Capacidad de enfrentar situaciones que pueden afectar la integridad del servidor público. Capacidad para orientarse a los resultados. Actitud para finalizar las tareas y cumplir los objetivos, aún en situaciones más exigentes en cuanto a plazos',
                'min_score' => 0,
                'max_score' => 12.5,
                'weight' => 0.25,
                'order' => 3,
                'requires_comment' => false,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    12.5 pts: Demuestra sólida ética profesional, alta orientación a resultados y actitud proactiva
                    10.0 pts: Buena ética y actitud orientada al cumplimiento
                    7.5 pts: Ética y actitud adecuadas
                    5.0 pts: Ética y actitud aceptables con observaciones menores
                    2.5 pts: Dudas sobre ética o actitud
                    0 pts: Actitud inapropiada o falta de ética profesional
                ',
            ],
            [
                'code' => 'INT_COMMUNICATION',
                'name' => 'Comunicación',
                'description' => 'Capacidad de expresar oralmente sus ideas, información y opiniones de forma clara y comprensible, escuchando y siendo receptivo a las propuestas de los demás',
                'min_score' => 0,
                'max_score' => 12.5,
                'weight' => 0.25,
                'order' => 4,
                'requires_comment' => false,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    12.5 pts: Excelente comunicación, clara, persuasiva y receptiva
                    10.0 pts: Buena comunicación y expresión oral
                    7.5 pts: Comunicación adecuada
                    5.0 pts: Comunicación aceptable con algunas dificultades
                    2.5 pts: Dificultades de expresión y escucha
                    0 pts: Comunicación deficiente
                ',
            ],
        ];

        foreach ($interviewCriteria as $criterion) {
            EvaluationCriterion::updateOrCreate(
                ['code' => $criterion['code']],
                array_merge($criterion, [
                    'phase_id' => $interviewPhase->id,
                    'is_active' => true,
                    'is_system' => true,
                ])
            );
        }

        $this->command->info('✅ Criterios de evaluación creados correctamente');
        $this->command->info("   - Entrevista Personal: " . count($interviewCriteria) . " criterios");
    }
}