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
        $cvEvaluationPhase = ProcessPhase::where('code', 'PHASE_06_CV_EVALUATION')->first();
        $interviewPhase = ProcessPhase::where('code', 'PHASE_08_INTERVIEW')->first();

        if (!$cvEvaluationPhase || !$interviewPhase) {
            $this->command->warn('⚠️  Fases de evaluación no encontradas. Ejecuta primero ProcessPhasesSeeder.');
            return;
        }

        // Criterios para Evaluación Curricular (PHASE_06)
        $cvCriteria = [
            [
                'code' => 'CV_EDUCATION',
                'name' => 'Formación Académica',
                'description' => 'Evaluación de títulos, grados académicos y formación relacionada al puesto',
                'min_score' => 0,
                'max_score' => 20,
                'weight' => 0.30,
                'order' => 1,
                'requires_comment' => true,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    20 pts: Título profesional + postgrado relacionado
                    15 pts: Título profesional relacionado
                    10 pts: Título técnico superior
                    5 pts: Estudios técnicos o en curso
                    0 pts: No cumple requisito mínimo
                ',
            ],
            [
                'code' => 'CV_EXPERIENCE',
                'name' => 'Experiencia Laboral',
                'description' => 'Años y tipo de experiencia en funciones similares',
                'min_score' => 0,
                'max_score' => 25,
                'weight' => 0.40,
                'order' => 2,
                'requires_comment' => true,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    25 pts: Más de 5 años en funciones similares
                    20 pts: 3-5 años en funciones similares
                    15 pts: 2-3 años en funciones similares
                    10 pts: 1-2 años de experiencia general
                    5 pts: Menos de 1 año
                    0 pts: Sin experiencia
                ',
            ],
            [
                'code' => 'CV_TRAINING',
                'name' => 'Capacitaciones y Certificaciones',
                'description' => 'Cursos, diplomados, certificaciones relacionadas al puesto',
                'min_score' => 0,
                'max_score' => 15,
                'weight' => 0.20,
                'order' => 3,
                'requires_comment' => false,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    15 pts: Más de 200 horas de capacitación certificada
                    10 pts: 100-200 horas de capacitación
                    5 pts: 50-100 horas de capacitación
                    2 pts: Menos de 50 horas
                    0 pts: Sin capacitaciones
                ',
            ],
            [
                'code' => 'CV_LANGUAGES',
                'name' => 'Idiomas',
                'description' => 'Conocimiento de idiomas extranjeros (si aplica)',
                'min_score' => 0,
                'max_score' => 5,
                'weight' => 0.05,
                'order' => 4,
                'requires_comment' => false,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    5 pts: Nivel avanzado (certificado)
                    3 pts: Nivel intermedio
                    1 pts: Nivel básico
                    0 pts: No aplica
                ',
            ],
            [
                'code' => 'CV_TECHNOLOGY',
                'name' => 'Conocimientos Técnicos',
                'description' => 'Manejo de herramientas, software o tecnologías específicas',
                'min_score' => 0,
                'max_score' => 10,
                'weight' => 0.10,
                'order' => 5,
                'requires_comment' => true,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    10 pts: Dominio avanzado de herramientas requeridas
                    7 pts: Conocimiento intermedio
                    4 pts: Conocimiento básico
                    0 pts: Sin conocimientos
                ',
            ],
        ];

        foreach ($cvCriteria as $criterion) {
            EvaluationCriterion::updateOrCreate(
                ['code' => $criterion['code']],
                array_merge($criterion, [
                    'phase_id' => $cvEvaluationPhase->id,
                    'is_active' => true,
                    'is_system' => true,
                ])
            );
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
                'requires_comment' => true,
                'requires_evidence' => true,
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
                'requires_comment' => true,
                'requires_evidence' => true,
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
                'requires_comment' => true,
                'requires_evidence' => true,
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
                'requires_comment' => true,
                'requires_evidence' => true,
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
        $this->command->info("   - Evaluación Curricular: " . count($cvCriteria) . " criterios");
        $this->command->info("   - Entrevista Personal: " . count($interviewCriteria) . " criterios");
    }
}