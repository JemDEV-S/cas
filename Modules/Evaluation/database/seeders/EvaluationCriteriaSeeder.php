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

        // Criterios para Entrevista Personal (PHASE_08)
        $interviewCriteria = [
            [
                'code' => 'INT_KNOWLEDGE',
                'name' => 'Conocimientos del Puesto',
                'description' => 'Dominio de conocimientos técnicos y teóricos relacionados al cargo',
                'min_score' => 0,
                'max_score' => 25,
                'weight' => 0.30,
                'order' => 1,
                'requires_comment' => true,
                'requires_evidence' => true,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    25 pts: Demuestra dominio excepcional
                    20 pts: Conocimiento sólido y preciso
                    15 pts: Conocimiento adecuado
                    10 pts: Conocimiento básico
                    5 pts: Conocimiento insuficiente
                    0 pts: No demuestra conocimientos
                ',
            ],
            [
                'code' => 'INT_COMMUNICATION',
                'name' => 'Habilidades de Comunicación',
                'description' => 'Expresión oral, claridad, capacidad de argumentación',
                'min_score' => 0,
                'max_score' => 20,
                'weight' => 0.25,
                'order' => 2,
                'requires_comment' => true,
                'requires_evidence' => true,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    20 pts: Excelente comunicación, clara y persuasiva
                    15 pts: Buena comunicación y expresión
                    10 pts: Comunicación adecuada
                    5 pts: Dificultades de expresión
                    0 pts: Comunicación deficiente
                ',
            ],
            [
                'code' => 'INT_PROBLEM_SOLVING',
                'name' => 'Resolución de Problemas',
                'description' => 'Capacidad analítica y pensamiento crítico',
                'min_score' => 0,
                'max_score' => 20,
                'weight' => 0.25,
                'order' => 3,
                'requires_comment' => true,
                'requires_evidence' => true,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    20 pts: Análisis excepcional, soluciones innovadoras
                    15 pts: Buen análisis y propuestas viables
                    10 pts: Análisis básico adecuado
                    5 pts: Dificultad para analizar situaciones
                    0 pts: No puede resolver problemas planteados
                ',
            ],
            [
                'code' => 'INT_TEAMWORK',
                'name' => 'Trabajo en Equipo',
                'description' => 'Actitud colaborativa y habilidades interpersonales',
                'min_score' => 0,
                'max_score' => 15,
                'weight' => 0.15,
                'order' => 4,
                'requires_comment' => true,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    15 pts: Excelente orientación al trabajo en equipo
                    10 pts: Buena disposición colaborativa
                    5 pts: Actitud neutral hacia el trabajo en equipo
                    2 pts: Prefiere trabajo individual
                    0 pts: Dificultades para trabajar en equipo
                ',
            ],
            [
                'code' => 'INT_MOTIVATION',
                'name' => 'Motivación y Compromiso',
                'description' => 'Interés genuino por el puesto y la institución',
                'min_score' => 0,
                'max_score' => 10,
                'weight' => 0.10,
                'order' => 5,
                'requires_comment' => true,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    10 pts: Alta motivación y compromiso evidente
                    7 pts: Buena motivación e interés
                    5 pts: Motivación adecuada
                    2 pts: Motivación dudosa
                    0 pts: Sin interés real
                ',
            ],
            [
                'code' => 'INT_PRESENTATION',
                'name' => 'Presentación Personal',
                'description' => 'Presencia, puntualidad y profesionalismo',
                'min_score' => 0,
                'max_score' => 10,
                'weight' => 0.10,
                'order' => 6,
                'requires_comment' => false,
                'requires_evidence' => false,
                'score_type' => 'NUMERIC',
                'evaluation_guide' => '
                    10 pts: Excelente presentación y profesionalismo
                    7 pts: Buena presentación
                    5 pts: Presentación adecuada
                    2 pts: Presentación deficiente
                    0 pts: Presentación inapropiada
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