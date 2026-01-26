# Documentacion de Implementacion: Entrevistas, Bonificaciones y Resultados Finales

## Sistema CAS - Municipalidad (Peru)

**Fecha:** 2026-01-26
**Modulos:** Results / Evaluation / Application
**Fases del Proceso:** FASE 8 (Entrevista) → FASE 9 (Resultados Finales)

---

## 1. CONTEXTO Y MARCO LEGAL

### 1.1 Normativa Aplicable

| Ley | Concepto | Bonificacion |
|-----|----------|--------------|
| **Ley 31533, Art. 3.1** | Postulantes tecnicos/profesionales menores de 29 anios | 10% sobre puntaje de entrevista |
| **Ley 31533, Art. 3.2** | Experiencia laboral en sector publico | +1 punto por anio (max 3 pts) |
| **Ley 29973** | Persona con discapacidad certificada | 15% sobre puntaje final |
| **Ley 29248** | Licenciado de las FF.AA. | 10% sobre puntaje final |
| **Ley 27674** | Deportista calificado nacional | 10% sobre puntaje final |
| **Ley 27674** | Deportista calificado internacional | 15% sobre puntaje final |
| **Ley 27277** | Victima del terrorismo | 10% sobre puntaje final |

### 1.2 Situacion Actual del Sistema

| Componente | Estado | Ubicacion |
|------------|--------|-----------|
| Fase 8 Entrevista | ✅ Definida | `PHASE_08_INTERVIEW` |
| Criterios Entrevista (6) | ✅ Creados | EvaluationCriteriaSeeder |
| Campo `interview_score` | ✅ Existe | Application (vacio) |
| Campo `final_score` | ✅ Existe | Application (vacio) |
| Condiciones Especiales | ✅ Implementado | 5 tipos con % |
| Campo `is_public_sector` | ✅ Existe | ApplicationExperience |
| Bonificacion por edad | ❌ Falta | No implementado |
| Bonificacion exp. publica | ❌ Falta | No implementado |
| Calculo puntaje final | ❌ Falta | No implementado |
| Asignacion de ganadores | ❌ Falta | No implementado |

---

## 2. REGLAS DE NEGOCIO

### 2.1 Formula de Puntaje Final

```
PUNTAJE_BASE = curriculum_score + interview_score_con_bonus_joven

Donde:
- curriculum_score: 0 a 50 puntos (minimo 35 para aprobar)
- interview_score: 0 a 50 puntos (minimo 35 para aprobar)
- bonus_joven: 10% sobre interview_score (si edad < 29)

PUNTAJE_FINAL = PUNTAJE_BASE + BONIFICACIONES

Bonificaciones (acumulables):
- Discapacidad: 15% de PUNTAJE_BASE
- Licenciado FF.AA.: 10% de PUNTAJE_BASE
- Deportista Nacional: 10% de PUNTAJE_BASE
- Deportista Internacional: 15% de PUNTAJE_BASE
- Victima Terrorismo: 10% de PUNTAJE_BASE
- Experiencia Sector Publico: +1 pt/anio (max 3 pts)

NOTA: El puntaje final PUEDE superar 100 puntos
```

### 2.2 Ejemplo de Calculo

```
Postulante: Juan Perez, 27 anios, con discapacidad, 4 anios sector publico

curriculum_score = 45/50
interview_score_raw = 42/50

1. Bonus Joven (< 29 anios):
   bonus_joven = 42 * 0.10 = 4.2
   interview_score_final = 42 + 4.2 = 46.2

2. Puntaje Base:
   puntaje_base = 45 + 46.2 = 91.2

3. Bonificacion Discapacidad (15%):
   bonus_discapacidad = 91.2 * 0.15 = 13.68

4. Bonificacion Exp. Sector Publico (4 anios, max 3 pts):
   bonus_exp_publica = 3.0 (tope)

5. PUNTAJE FINAL:
   final_score = 91.2 + 13.68 + 3.0 = 107.88 puntos
```

### 2.3 Reglas de Aprobacion

| Etapa | Puntaje Minimo | Puntaje Maximo | Estado si no aprueba |
|-------|----------------|----------------|----------------------|
| Evaluacion CV | 35 pts | 50 pts | NO_APTO |
| Entrevista | 35 pts | 50 pts | NO_APTO |
| Puntaje Final | 70 pts | Sin limite | NO_APTO |

### 2.4 Asignacion de Ganadores y Accesitarios

```
Por cada JobProfile (puesto/perfil):
1. Ordenar postulantes por final_score DESC
2. Asignar vacantes a los N mejores (N = numero de vacantes del perfil)
3. Marcar siguientes M como accesitarios (M = configurable, ej: 2)
4. Resto queda como "no seleccionado"

Estados resultantes:
- GANADOR: Obtiene vacante
- ACCESITARIO: Puede ocupar si ganador desiste
- NO_SELECCIONADO: No obtiene vacante pero aprobo
```

---

## 3. NUEVOS CAMPOS REQUERIDOS

### 3.1 Migracion: Campos de Bonificacion en Application

```php
// database/migrations/xxxx_add_bonus_fields_to_applications_table.php

Schema::table('applications', function (Blueprint $table) {
    // Bonificacion por edad (Ley 31533)
    $table->decimal('age_bonus', 5, 2)->nullable()->after('interview_score');

    // Bonificacion por experiencia sector publico (Ley 31533)
    $table->decimal('public_sector_bonus', 5, 2)->nullable()->after('age_bonus');

    // Anios de experiencia en sector publico (calculado)
    $table->integer('public_sector_years')->nullable()->after('public_sector_bonus');

    // Puntaje de entrevista con bonus joven aplicado
    $table->decimal('interview_score_with_bonus', 5, 2)->nullable()->after('public_sector_years');

    // Puntaje base (CV + Entrevista con bonus)
    $table->decimal('base_score', 5, 2)->nullable()->after('interview_score_with_bonus');

    // Total de bonificaciones especiales (discapacidad, militar, etc)
    $table->decimal('special_bonus_total', 5, 2)->nullable()->after('base_score');

    // Resultado de seleccion
    $table->enum('selection_result', ['GANADOR', 'ACCESITARIO', 'NO_SELECCIONADO', 'NO_APTO'])
          ->nullable()->after('final_ranking');

    // Orden de accesitario (1, 2, 3...)
    $table->integer('accesitario_order')->nullable()->after('selection_result');
});
```

### 3.2 Campos Existentes a Utilizar

| Campo | Tabla | Tipo | Uso |
|-------|-------|------|-----|
| `birth_date` | applications | date | Calcular edad para bonus joven |
| `is_public_sector` | application_experiences | boolean | Identificar exp. sector publico |
| `duration_days` | application_experiences | integer | Calcular anios de experiencia |
| `special_condition_bonus` | applications | decimal | Bonificacion discapacidad/militar/etc |

---

## 4. DISENO DE LA SOLUCION

### 4.1 Nuevas Rutas

```php
// Modules/Results/routes/web.php

// Procesamiento de Entrevistas (Fase 8)
Route::get('postings/{posting}/results/interview-processing',
    [InterviewResultProcessingController::class, 'index'])
    ->name('interview-processing');

Route::post('postings/{posting}/results/interview-processing/preview',
    [InterviewResultProcessingController::class, 'preview'])
    ->name('interview-processing.preview');

Route::post('postings/{posting}/results/interview-processing/execute',
    [InterviewResultProcessingController::class, 'execute'])
    ->name('interview-processing.execute');

// Calculo de Resultados Finales (Fase 9)
Route::get('postings/{posting}/results/final-calculation',
    [FinalResultCalculationController::class, 'index'])
    ->name('final-calculation');

Route::post('postings/{posting}/results/final-calculation/preview',
    [FinalResultCalculationController::class, 'preview'])
    ->name('final-calculation.preview');

Route::post('postings/{posting}/results/final-calculation/execute',
    [FinalResultCalculationController::class, 'execute'])
    ->name('final-calculation.execute');

// Asignacion de Ganadores
Route::get('postings/{posting}/results/winner-assignment',
    [WinnerAssignmentController::class, 'index'])
    ->name('winner-assignment');

Route::post('postings/{posting}/results/winner-assignment/preview',
    [WinnerAssignmentController::class, 'preview'])
    ->name('winner-assignment.preview');

Route::post('postings/{posting}/results/winner-assignment/execute',
    [WinnerAssignmentController::class, 'execute'])
    ->name('winner-assignment.execute');
```

### 4.2 Estructura de Archivos

```
Modules/Results/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Admin/
│   │           ├── CvResultProcessingController.php      (existente)
│   │           ├── InterviewResultProcessingController.php  ← NUEVO
│   │           ├── FinalResultCalculationController.php     ← NUEVO
│   │           └── WinnerAssignmentController.php           ← NUEVO
│   └── Services/
│       ├── CvResultProcessingService.php                 (existente)
│       ├── InterviewResultProcessingService.php             ← NUEVO
│       ├── FinalScoreCalculationService.php                 ← NUEVO
│       ├── BonusCalculationService.php                      ← NUEVO
│       └── WinnerAssignmentService.php                      ← NUEVO
└── resources/
    └── views/
        └── admin/
            ├── cv-processing/                            (existente)
            ├── interview-processing/                        ← NUEVO
            │   ├── index.blade.php
            │   └── preview.blade.php
            ├── final-calculation/                           ← NUEVO
            │   ├── index.blade.php
            │   └── preview.blade.php
            └── winner-assignment/                           ← NUEVO
                ├── index.blade.php
                └── preview.blade.php
```

---

## 5. SERVICIOS PRINCIPALES

### 5.1 BonusCalculationService

```php
<?php

namespace Modules\Results\Services;

use Carbon\Carbon;
use Modules\Application\Entities\Application;

class BonusCalculationService
{
    const MAX_PUBLIC_SECTOR_BONUS = 3.0;
    const AGE_LIMIT_FOR_BONUS = 29;
    const AGE_BONUS_PERCENTAGE = 0.10;

    /**
     * Calcular todas las bonificaciones para una postulacion
     */
    public function calculateAllBonuses(Application $application): array
    {
        $interviewScore = $application->interview_score ?? 0;

        // 1. Bonus por edad (< 29 anios) - sobre entrevista
        $ageBonus = $this->calculateAgeBonus($application, $interviewScore);

        // 2. Interview score con bonus joven
        $interviewScoreWithBonus = $interviewScore + $ageBonus;

        // 3. Puntaje base (CV + Entrevista con bonus)
        $baseScore = ($application->curriculum_score ?? 0) + $interviewScoreWithBonus;

        // 4. Bonus por experiencia sector publico
        $publicSectorYears = $this->calculatePublicSectorYears($application);
        $publicSectorBonus = min($publicSectorYears, self::MAX_PUBLIC_SECTOR_BONUS);

        // 5. Bonificaciones especiales (discapacidad, militar, etc) - sobre puntaje base
        $specialBonuses = $this->calculateSpecialBonuses($application, $baseScore);

        // 6. Puntaje final
        $finalScore = $baseScore + $specialBonuses['total'] + $publicSectorBonus;

        return [
            'interview_score_raw' => $interviewScore,
            'age_bonus' => round($ageBonus, 2),
            'interview_score_with_bonus' => round($interviewScoreWithBonus, 2),
            'curriculum_score' => $application->curriculum_score ?? 0,
            'base_score' => round($baseScore, 2),
            'public_sector_years' => $publicSectorYears,
            'public_sector_bonus' => round($publicSectorBonus, 2),
            'special_bonuses' => $specialBonuses,
            'special_bonus_total' => round($specialBonuses['total'], 2),
            'final_score' => round($finalScore, 2),
            'is_approved' => $finalScore >= 70,
        ];
    }

    /**
     * Calcular bonus por edad (Ley 31533, Art. 3.1)
     * 10% sobre puntaje de entrevista para menores de 29 anios
     */
    public function calculateAgeBonus(Application $application, float $interviewScore): float
    {
        if (!$application->birth_date) {
            return 0;
        }

        $age = Carbon::parse($application->birth_date)->age;

        if ($age < self::AGE_LIMIT_FOR_BONUS) {
            return $interviewScore * self::AGE_BONUS_PERCENTAGE;
        }

        return 0;
    }

    /**
     * Calcular anios de experiencia en sector publico
     */
    public function calculatePublicSectorYears(Application $application): int
    {
        $totalDays = $application->experiences()
            ->where('is_public_sector', true)
            ->where('is_verified', true)
            ->sum('duration_days');

        // Convertir dias a anios (365 dias = 1 anio)
        return (int) floor($totalDays / 365);
    }

    /**
     * Calcular bonificaciones especiales (sobre puntaje base)
     */
    public function calculateSpecialBonuses(Application $application, float $baseScore): array
    {
        $bonuses = [
            'disability' => 0,      // Discapacidad 15%
            'military' => 0,        // Licenciado FF.AA. 10%
            'athlete_national' => 0, // Deportista nacional 10%
            'athlete_intl' => 0,    // Deportista internacional 15%
            'terrorism' => 0,       // Victima terrorismo 10%
            'total' => 0,
            'details' => [],
        ];

        foreach ($application->specialConditions as $condition) {
            if (!$condition->is_verified || !$condition->isValid()) {
                continue;
            }

            $percentage = $condition->bonus_percentage / 100;
            $bonus = $baseScore * $percentage;

            switch ($condition->condition_type) {
                case 'DISABILITY':
                    $bonuses['disability'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Discapacidad',
                        'law' => 'Ley 29973',
                        'percentage' => $condition->bonus_percentage,
                        'amount' => round($bonus, 2),
                    ];
                    break;
                case 'MILITARY':
                    $bonuses['military'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Licenciado FF.AA.',
                        'law' => 'Ley 29248',
                        'percentage' => $condition->bonus_percentage,
                        'amount' => round($bonus, 2),
                    ];
                    break;
                case 'ATHLETE_NATIONAL':
                    $bonuses['athlete_national'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Deportista Nacional',
                        'law' => 'Ley 27674',
                        'percentage' => $condition->bonus_percentage,
                        'amount' => round($bonus, 2),
                    ];
                    break;
                case 'ATHLETE_INTL':
                    $bonuses['athlete_intl'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Deportista Internacional',
                        'law' => 'Ley 27674',
                        'percentage' => $condition->bonus_percentage,
                        'amount' => round($bonus, 2),
                    ];
                    break;
                case 'TERRORISM':
                    $bonuses['terrorism'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Victima Terrorismo',
                        'law' => 'Ley 27277',
                        'percentage' => $condition->bonus_percentage,
                        'amount' => round($bonus, 2),
                    ];
                    break;
            }
        }

        // Sumar todas las bonificaciones (son acumulables)
        $bonuses['total'] = $bonuses['disability'] + $bonuses['military'] +
                           $bonuses['athlete_national'] + $bonuses['athlete_intl'] +
                           $bonuses['terrorism'];

        return $bonuses;
    }

    /**
     * Obtener edad del postulante
     */
    public function getAge(Application $application): ?int
    {
        if (!$application->birth_date) {
            return null;
        }

        return Carbon::parse($application->birth_date)->age;
    }
}
```

### 5.2 InterviewResultProcessingService

```php
<?php

namespace Modules\Results\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;
use Modules\Evaluation\Entities\Evaluation;
use Modules\Evaluation\Enums\EvaluationStatusEnum;
use Modules\JobPosting\Entities\JobPosting;

class InterviewResultProcessingService
{
    const MIN_PASSING_SCORE = 35;
    const MAX_SCORE = 50;

    public function __construct(
        private BonusCalculationService $bonusService
    ) {}

    /**
     * Obtener resumen del estado actual
     */
    public function getSummary(JobPosting $posting): array
    {
        // Postulaciones que pasaron CV (curriculum_score >= 35)
        $applications = $this->getEligibleForInterviewApplications($posting);

        $interviewPhase = $this->getInterviewPhase();

        $withEvaluation = 0;
        $withoutEvaluation = 0;
        $evaluationsSubmitted = 0;
        $evaluationsPending = 0;
        $alreadyProcessed = 0;

        foreach ($applications as $app) {
            $evaluation = Evaluation::where('application_id', $app->id)
                ->where('phase_id', $interviewPhase?->id)
                ->first();

            if (!$evaluation) {
                $withoutEvaluation++;
            } else {
                $withEvaluation++;
                if ($evaluation->isCompleted()) {
                    $evaluationsSubmitted++;
                } else {
                    $evaluationsPending++;
                }
            }

            if ($app->interview_score !== null) {
                $alreadyProcessed++;
            }
        }

        return [
            'total_eligible_for_interview' => $applications->count(),
            'with_evaluation' => $withEvaluation,
            'without_evaluation' => $withoutEvaluation,
            'evaluations_submitted' => $evaluationsSubmitted,
            'evaluations_pending' => $evaluationsPending,
            'already_processed' => $alreadyProcessed,
            'interview_phase' => $interviewPhase,
        ];
    }

    /**
     * Preview (dry-run) del procesamiento
     */
    public function preview(JobPosting $posting): array
    {
        $applications = $this->getEligibleForInterviewApplications($posting);
        $interviewPhase = $this->getInterviewPhase();

        $preview = [
            'will_pass' => [],
            'will_fail' => [],
            'no_evaluation' => [],
        ];

        foreach ($applications as $app) {
            $evaluation = Evaluation::where('application_id', $app->id)
                ->where('phase_id', $interviewPhase?->id)
                ->whereIn('status', [
                    EvaluationStatusEnum::SUBMITTED,
                    EvaluationStatusEnum::MODIFIED,
                ])
                ->first();

            if (!$evaluation) {
                $preview['no_evaluation'][] = [
                    'application' => $app,
                    'reason' => 'Sin evaluacion de entrevista completada',
                ];
                continue;
            }

            $score = $evaluation->total_score ?? 0;
            $age = $this->bonusService->getAge($app);
            $ageBonus = $this->bonusService->calculateAgeBonus($app, $score);

            $item = [
                'application' => $app,
                'evaluation' => $evaluation,
                'score_raw' => $score,
                'age' => $age,
                'age_bonus' => round($ageBonus, 2),
                'score_with_bonus' => round($score + $ageBonus, 2),
                'is_reprocess' => $app->interview_score !== null,
                'evaluator' => $evaluation->evaluator?->name ?? 'N/A',
                'comments' => $evaluation->general_comments,
            ];

            if ($score >= self::MIN_PASSING_SCORE) {
                $item['new_status'] = 'Mantiene APTO';
                $preview['will_pass'][] = $item;
            } else {
                $item['new_status'] = 'Cambia a NO_APTO';
                $item['reason'] = "Puntaje entrevista ({$score}/50) menor al minimo (35)";
                $preview['will_fail'][] = $item;
            }
        }

        // Ordenar por puntaje
        usort($preview['will_pass'], fn($a, $b) => $b['score_with_bonus'] <=> $a['score_with_bonus']);
        usort($preview['will_fail'], fn($a, $b) => $b['score_raw'] <=> $a['score_raw']);

        $preview['summary'] = [
            'total_to_process' => count($preview['will_pass']) + count($preview['will_fail']),
            'will_pass_count' => count($preview['will_pass']),
            'will_fail_count' => count($preview['will_fail']),
            'no_evaluation_count' => count($preview['no_evaluation']),
        ];

        return $preview;
    }

    /**
     * Ejecutar procesamiento real
     */
    public function execute(JobPosting $posting): array
    {
        return DB::transaction(function () use ($posting) {
            $applications = $this->getEligibleForInterviewApplications($posting);
            $interviewPhase = $this->getInterviewPhase();

            $processed = 0;
            $passed = 0;
            $failed = 0;
            $skipped = 0;
            $errors = [];

            foreach ($applications as $app) {
                try {
                    $evaluation = Evaluation::where('application_id', $app->id)
                        ->where('phase_id', $interviewPhase?->id)
                        ->whereIn('status', [
                            EvaluationStatusEnum::SUBMITTED,
                            EvaluationStatusEnum::MODIFIED,
                        ])
                        ->first();

                    if (!$evaluation) {
                        $skipped++;
                        continue;
                    }

                    $score = $evaluation->total_score ?? 0;
                    $ageBonus = $this->bonusService->calculateAgeBonus($app, $score);

                    // Actualizar puntajes
                    $app->interview_score = $score;
                    $app->age_bonus = $ageBonus;
                    $app->interview_score_with_bonus = $score + $ageBonus;

                    // Determinar estado
                    if ($score >= self::MIN_PASSING_SCORE) {
                        // Mantiene APTO
                        $passed++;
                    } else {
                        // No aprobo entrevista
                        $app->status = ApplicationStatus::NOT_ELIGIBLE;
                        $app->is_eligible = false;
                        $app->ineligibility_reason = $evaluation->general_comments
                            ?: "Puntaje entrevista ({$score}/50) menor al minimo (35)";
                        $failed++;
                    }

                    $app->save();
                    $this->logProcessing($app, $score, $ageBonus);
                    $processed++;

                } catch (\Exception $e) {
                    $errors[] = ['application_id' => $app->id, 'error' => $e->getMessage()];
                }
            }

            Log::info('Procesamiento de entrevistas ejecutado', [
                'job_posting_id' => $posting->id,
                'processed' => $processed,
                'passed' => $passed,
                'failed' => $failed,
            ]);

            return compact('processed', 'passed', 'failed', 'skipped', 'errors');
        });
    }

    /**
     * Postulaciones elegibles para entrevista (aprobaron CV)
     */
    private function getEligibleForInterviewApplications(JobPosting $posting)
    {
        return Application::whereHas('jobProfile', fn($q) =>
                $q->where('job_posting_id', $posting->id)
            )
            ->where('status', ApplicationStatus::ELIGIBLE)
            ->where('is_eligible', true)
            ->whereNotNull('curriculum_score')
            ->where('curriculum_score', '>=', 35)
            ->with(['jobProfile.positionCode', 'applicant', 'specialConditions'])
            ->orderBy('full_name')
            ->get();
    }

    private function getInterviewPhase()
    {
        return \Modules\JobPosting\Entities\ProcessPhase::where('code', 'PHASE_08_INTERVIEW')->first();
    }

    private function logProcessing($application, $score, $ageBonus): void
    {
        $application->history()->create([
            'action_type' => 'INTERVIEW_RESULT_PROCESSED',
            'description' => "Entrevista procesada: {$score}/50 + bonus joven: {$ageBonus}",
            'performed_by' => auth()->id(),
            'performed_at' => now(),
            'metadata' => [
                'interview_score' => $score,
                'age_bonus' => $ageBonus,
                'interview_score_with_bonus' => $score + $ageBonus,
            ],
        ]);
    }
}
```

### 5.3 FinalScoreCalculationService

```php
<?php

namespace Modules\Results\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;
use Modules\JobPosting\Entities\JobPosting;

class FinalScoreCalculationService
{
    const MIN_FINAL_SCORE = 70;

    public function __construct(
        private BonusCalculationService $bonusService
    ) {}

    /**
     * Obtener resumen
     */
    public function getSummary(JobPosting $posting): array
    {
        $applications = $this->getEligibleApplications($posting);

        $readyForCalculation = 0;
        $missingInterview = 0;
        $alreadyCalculated = 0;

        foreach ($applications as $app) {
            if ($app->interview_score === null) {
                $missingInterview++;
            } else {
                $readyForCalculation++;
            }

            if ($app->final_score !== null) {
                $alreadyCalculated++;
            }
        }

        return [
            'total_eligible' => $applications->count(),
            'ready_for_calculation' => $readyForCalculation,
            'missing_interview' => $missingInterview,
            'already_calculated' => $alreadyCalculated,
        ];
    }

    /**
     * Preview del calculo
     */
    public function preview(JobPosting $posting): array
    {
        $applications = $this->getEligibleApplications($posting);

        $preview = [
            'will_approve' => [],
            'will_fail' => [],
            'incomplete' => [],
        ];

        foreach ($applications as $app) {
            if ($app->interview_score === null) {
                $preview['incomplete'][] = [
                    'application' => $app,
                    'reason' => 'Falta puntaje de entrevista',
                ];
                continue;
            }

            $bonuses = $this->bonusService->calculateAllBonuses($app);

            $item = [
                'application' => $app,
                'curriculum_score' => $app->curriculum_score,
                'interview_score_raw' => $app->interview_score,
                'age_bonus' => $bonuses['age_bonus'],
                'interview_score_with_bonus' => $bonuses['interview_score_with_bonus'],
                'base_score' => $bonuses['base_score'],
                'public_sector_years' => $bonuses['public_sector_years'],
                'public_sector_bonus' => $bonuses['public_sector_bonus'],
                'special_bonuses' => $bonuses['special_bonuses'],
                'special_bonus_total' => $bonuses['special_bonus_total'],
                'final_score' => $bonuses['final_score'],
                'is_reprocess' => $app->final_score !== null,
            ];

            if ($bonuses['is_approved']) {
                $item['status'] = 'APROBADO';
                $preview['will_approve'][] = $item;
            } else {
                $item['status'] = 'NO_APTO';
                $item['reason'] = "Puntaje final ({$bonuses['final_score']}) menor al minimo (70)";
                $preview['will_fail'][] = $item;
            }
        }

        // Ordenar por puntaje final
        usort($preview['will_approve'], fn($a, $b) => $b['final_score'] <=> $a['final_score']);
        usort($preview['will_fail'], fn($a, $b) => $b['final_score'] <=> $a['final_score']);

        $preview['summary'] = [
            'will_approve_count' => count($preview['will_approve']),
            'will_fail_count' => count($preview['will_fail']),
            'incomplete_count' => count($preview['incomplete']),
            'min_score_required' => self::MIN_FINAL_SCORE,
        ];

        return $preview;
    }

    /**
     * Ejecutar calculo
     */
    public function execute(JobPosting $posting): array
    {
        return DB::transaction(function () use ($posting) {
            $applications = $this->getEligibleApplications($posting);

            $processed = 0;
            $approved = 0;
            $failed = 0;
            $skipped = 0;
            $errors = [];

            foreach ($applications as $app) {
                try {
                    if ($app->interview_score === null) {
                        $skipped++;
                        continue;
                    }

                    $bonuses = $this->bonusService->calculateAllBonuses($app);

                    // Actualizar todos los campos
                    $app->age_bonus = $bonuses['age_bonus'];
                    $app->interview_score_with_bonus = $bonuses['interview_score_with_bonus'];
                    $app->base_score = $bonuses['base_score'];
                    $app->public_sector_years = $bonuses['public_sector_years'];
                    $app->public_sector_bonus = $bonuses['public_sector_bonus'];
                    $app->special_bonus_total = $bonuses['special_bonus_total'];
                    $app->final_score = $bonuses['final_score'];

                    // Determinar estado final
                    if ($bonuses['is_approved']) {
                        // Mantiene APTO, listo para seleccion
                        $approved++;
                    } else {
                        // No alcanzo puntaje minimo final
                        $app->status = ApplicationStatus::NOT_ELIGIBLE;
                        $app->is_eligible = false;
                        $app->ineligibility_reason = "Puntaje final ({$bonuses['final_score']}) menor al minimo (70)";
                        $failed++;
                    }

                    $app->save();
                    $this->logCalculation($app, $bonuses);
                    $processed++;

                } catch (\Exception $e) {
                    $errors[] = ['application_id' => $app->id, 'error' => $e->getMessage()];
                }
            }

            Log::info('Calculo de puntaje final ejecutado', [
                'job_posting_id' => $posting->id,
                'processed' => $processed,
                'approved' => $approved,
                'failed' => $failed,
            ]);

            return compact('processed', 'approved', 'failed', 'skipped', 'errors');
        });
    }

    private function getEligibleApplications(JobPosting $posting)
    {
        return Application::whereHas('jobProfile', fn($q) =>
                $q->where('job_posting_id', $posting->id)
            )
            ->where('status', ApplicationStatus::ELIGIBLE)
            ->where('is_eligible', true)
            ->whereNotNull('curriculum_score')
            ->where('curriculum_score', '>=', 35)
            ->with([
                'jobProfile.positionCode',
                'applicant',
                'specialConditions',
                'experiences' => fn($q) => $q->where('is_public_sector', true)->where('is_verified', true)
            ])
            ->orderBy('full_name')
            ->get();
    }

    private function logCalculation($application, $bonuses): void
    {
        $application->history()->create([
            'action_type' => 'FINAL_SCORE_CALCULATED',
            'description' => "Puntaje final calculado: {$bonuses['final_score']}",
            'performed_by' => auth()->id(),
            'performed_at' => now(),
            'metadata' => $bonuses,
        ]);
    }
}
```

### 5.4 WinnerAssignmentService

```php
<?php

namespace Modules\Results\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;
use Modules\JobPosting\Entities\JobPosting;
use Modules\JobProfile\Entities\JobProfile;

class WinnerAssignmentService
{
    const DEFAULT_ACCESITARIOS = 2;

    /**
     * Obtener resumen por perfil/puesto
     */
    public function getSummary(JobPosting $posting): array
    {
        $profiles = JobProfile::where('job_posting_id', $posting->id)
            ->with(['positionCode', 'requestingUnit', 'vacancies'])
            ->get();

        $summary = [];

        foreach ($profiles as $profile) {
            $vacancyCount = $profile->vacancies->count();

            $applicants = Application::where('job_profile_id', $profile->id)
                ->where('status', ApplicationStatus::ELIGIBLE)
                ->whereNotNull('final_score')
                ->where('final_score', '>=', 70)
                ->count();

            $summary[] = [
                'profile' => $profile,
                'position_code' => $profile->positionCode?->code,
                'position_name' => $profile->positionCode?->name,
                'unit' => $profile->requestingUnit?->name,
                'vacancies' => $vacancyCount,
                'eligible_applicants' => $applicants,
                'can_assign' => $applicants > 0,
            ];
        }

        return [
            'profiles' => $summary,
            'total_profiles' => count($summary),
            'total_vacancies' => collect($summary)->sum('vacancies'),
            'total_eligible' => collect($summary)->sum('eligible_applicants'),
        ];
    }

    /**
     * Preview de asignacion
     */
    public function preview(JobPosting $posting, int $accesitariosCount = self::DEFAULT_ACCESITARIOS): array
    {
        $profiles = JobProfile::where('job_posting_id', $posting->id)
            ->with(['positionCode', 'requestingUnit', 'vacancies'])
            ->get();

        $preview = [];

        foreach ($profiles as $profile) {
            $vacancyCount = $profile->vacancies->count();

            $applicants = Application::where('job_profile_id', $profile->id)
                ->where('status', ApplicationStatus::ELIGIBLE)
                ->whereNotNull('final_score')
                ->where('final_score', '>=', 70)
                ->orderByDesc('final_score')
                ->with('applicant')
                ->get();

            $winners = [];
            $accesitarios = [];
            $notSelected = [];

            foreach ($applicants as $index => $app) {
                $position = $index + 1;

                if ($position <= $vacancyCount) {
                    // Es ganador
                    $winners[] = [
                        'application' => $app,
                        'ranking' => $position,
                        'final_score' => $app->final_score,
                        'result' => 'GANADOR',
                        'vacancy_number' => $position,
                    ];
                } elseif ($position <= $vacancyCount + $accesitariosCount) {
                    // Es accesitario
                    $accesitarioOrder = $position - $vacancyCount;
                    $accesitarios[] = [
                        'application' => $app,
                        'ranking' => $position,
                        'final_score' => $app->final_score,
                        'result' => 'ACCESITARIO',
                        'accesitario_order' => $accesitarioOrder,
                    ];
                } else {
                    // No seleccionado
                    $notSelected[] = [
                        'application' => $app,
                        'ranking' => $position,
                        'final_score' => $app->final_score,
                        'result' => 'NO_SELECCIONADO',
                    ];
                }
            }

            $preview[] = [
                'profile' => $profile,
                'position_code' => $profile->positionCode?->code,
                'vacancies' => $vacancyCount,
                'total_applicants' => $applicants->count(),
                'winners' => $winners,
                'accesitarios' => $accesitarios,
                'not_selected' => $notSelected,
            ];
        }

        return [
            'profiles' => $preview,
            'accesitarios_count' => $accesitariosCount,
            'summary' => [
                'total_winners' => collect($preview)->sum(fn($p) => count($p['winners'])),
                'total_accesitarios' => collect($preview)->sum(fn($p) => count($p['accesitarios'])),
                'total_not_selected' => collect($preview)->sum(fn($p) => count($p['not_selected'])),
            ],
        ];
    }

    /**
     * Ejecutar asignacion
     */
    public function execute(JobPosting $posting, int $accesitariosCount = self::DEFAULT_ACCESITARIOS): array
    {
        return DB::transaction(function () use ($posting, $accesitariosCount) {
            $profiles = JobProfile::where('job_posting_id', $posting->id)
                ->with('vacancies')
                ->get();

            $totalWinners = 0;
            $totalAccesitarios = 0;
            $totalNotSelected = 0;
            $errors = [];

            foreach ($profiles as $profile) {
                try {
                    $vacancies = $profile->vacancies->values();
                    $vacancyCount = $vacancies->count();

                    $applicants = Application::where('job_profile_id', $profile->id)
                        ->where('status', ApplicationStatus::ELIGIBLE)
                        ->whereNotNull('final_score')
                        ->where('final_score', '>=', 70)
                        ->orderByDesc('final_score')
                        ->get();

                    foreach ($applicants as $index => $app) {
                        $position = $index + 1;
                        $app->final_ranking = $position;

                        if ($position <= $vacancyCount) {
                            // Ganador
                            $app->selection_result = 'GANADOR';
                            $app->status = ApplicationStatus::APPROVED;
                            $app->assigned_vacancy_id = $vacancies[$index]->id ?? null;
                            $totalWinners++;
                        } elseif ($position <= $vacancyCount + $accesitariosCount) {
                            // Accesitario
                            $app->selection_result = 'ACCESITARIO';
                            $app->accesitario_order = $position - $vacancyCount;
                            $totalAccesitarios++;
                        } else {
                            // No seleccionado
                            $app->selection_result = 'NO_SELECCIONADO';
                            $totalNotSelected++;
                        }

                        $app->save();
                        $this->logAssignment($app);
                    }
                } catch (\Exception $e) {
                    $errors[] = ['profile_id' => $profile->id, 'error' => $e->getMessage()];
                }
            }

            Log::info('Asignacion de ganadores ejecutada', [
                'job_posting_id' => $posting->id,
                'winners' => $totalWinners,
                'accesitarios' => $totalAccesitarios,
                'not_selected' => $totalNotSelected,
            ]);

            return [
                'winners' => $totalWinners,
                'accesitarios' => $totalAccesitarios,
                'not_selected' => $totalNotSelected,
                'errors' => $errors,
            ];
        });
    }

    private function logAssignment($application): void
    {
        $application->history()->create([
            'action_type' => 'SELECTION_RESULT_ASSIGNED',
            'description' => "Resultado: {$application->selection_result}, Ranking: {$application->final_ranking}",
            'performed_by' => auth()->id(),
            'performed_at' => now(),
            'metadata' => [
                'selection_result' => $application->selection_result,
                'final_ranking' => $application->final_ranking,
                'accesitario_order' => $application->accesitario_order,
                'assigned_vacancy_id' => $application->assigned_vacancy_id,
            ],
        ]);
    }
}
```

---

## 6. ACTUALIZACION DE EXCEL DE RESULTADOS FINALES

### 6.1 Nuevas Columnas

```php
// En ResultExportService::createPhase9Spreadsheet()

$headers = [
    'Ranking',
    'Codigo',
    'Apellidos y Nombres',
    'DNI',
    'Edad',
    'Puesto',
    'Puntaje CV',
    'Puntaje Entrevista',
    'Bonus Joven',
    'Entrevista + Bonus',
    'Puntaje Base',
    'Discapacidad',
    'Licenciado FF.AA.',
    'Deportista',
    'Victima Terrorismo',
    'Exp. Sector Publico',
    'Total Bonificaciones',
    'PUNTAJE FINAL',
    'Resultado',
    'Observaciones'
];
```

---

## 7. FLUJO COMPLETO DEL PROCESO

```
┌─────────────────────────────────────────────────────────────┐
│  FASE 6: Evaluacion Curricular                              │
│  → Procesar con CvResultProcessingService                   │
│  → curriculum_score: 0-50 (min 35 para aprobar)             │
│  → NO_APTO si < 35                                          │
└──────────────────────────┬──────────────────────────────────┘
                           ▼
┌─────────────────────────────────────────────────────────────┐
│  FASE 7: Publicacion Resultados CV                          │
│  → ResultPublicationService::publishPhase7Results()         │
└──────────────────────────┬──────────────────────────────────┘
                           ▼
┌─────────────────────────────────────────────────────────────┐
│  FASE 8: Entrevista Personal                                │
│  → Procesar con InterviewResultProcessingService            │
│  → interview_score: 0-50 (min 35 para aprobar)              │
│  → + age_bonus (10% si < 29 anios)                          │
│  → NO_APTO si < 35                                          │
└──────────────────────────┬──────────────────────────────────┘
                           ▼
┌─────────────────────────────────────────────────────────────┐
│  CALCULO DE PUNTAJE FINAL                                   │
│  → FinalScoreCalculationService                             │
│  → base_score = CV + (Entrevista + bonus_joven)             │
│  → + bonificaciones especiales (% sobre base)               │
│  → + exp. sector publico (max 3 pts)                        │
│  → NO_APTO si final_score < 70                              │
└──────────────────────────┬──────────────────────────────────┘
                           ▼
┌─────────────────────────────────────────────────────────────┐
│  ASIGNACION DE GANADORES                                    │
│  → WinnerAssignmentService                                  │
│  → Por cada perfil/puesto:                                  │
│    - Ordenar por final_score DESC                           │
│    - Top N = GANADORES (N = vacantes)                       │
│    - Siguientes M = ACCESITARIOS                            │
│    - Resto = NO_SELECCIONADO                                │
└──────────────────────────┬──────────────────────────────────┘
                           ▼
┌─────────────────────────────────────────────────────────────┐
│  FASE 9: Publicacion Resultados Finales                     │
│  → ResultPublicationService::publishPhase9Results()         │
│  → Documento PDF con firmas digitales                       │
│  → Excel con desglose de bonificaciones                     │
└─────────────────────────────────────────────────────────────┘
```

---

## 8. INTERFACES DE USUARIO

### 8.1 Dashboard de Resultados

Agregar tarjetas/botones para cada proceso:

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Procesar CV -->
    <a href="{{ route('admin.results.cv-processing', $posting) }}"
       class="bg-white p-4 rounded-lg shadow border hover:shadow-lg">
        <div class="text-blue-600 text-2xl mb-2"><i class="fas fa-file-alt"></i></div>
        <h3 class="font-semibold">Procesar CV</h3>
        <p class="text-sm text-gray-500">Fase 6 - Evaluacion Curricular</p>
    </a>

    <!-- Procesar Entrevistas -->
    <a href="{{ route('admin.results.interview-processing', $posting) }}"
       class="bg-white p-4 rounded-lg shadow border hover:shadow-lg">
        <div class="text-purple-600 text-2xl mb-2"><i class="fas fa-users"></i></div>
        <h3 class="font-semibold">Procesar Entrevistas</h3>
        <p class="text-sm text-gray-500">Fase 8 - Entrevista Personal</p>
    </a>

    <!-- Calcular Puntaje Final -->
    <a href="{{ route('admin.results.final-calculation', $posting) }}"
       class="bg-white p-4 rounded-lg shadow border hover:shadow-lg">
        <div class="text-green-600 text-2xl mb-2"><i class="fas fa-calculator"></i></div>
        <h3 class="font-semibold">Calcular Puntaje Final</h3>
        <p class="text-sm text-gray-500">Bonificaciones y Total</p>
    </a>

    <!-- Asignar Ganadores -->
    <a href="{{ route('admin.results.winner-assignment', $posting) }}"
       class="bg-white p-4 rounded-lg shadow border hover:shadow-lg">
        <div class="text-yellow-600 text-2xl mb-2"><i class="fas fa-trophy"></i></div>
        <h3 class="font-semibold">Asignar Ganadores</h3>
        <p class="text-sm text-gray-500">Ganadores y Accesitarios</p>
    </a>
</div>
```

---

## 9. MIGRACION COMPLETA

```php
<?php
// database/migrations/xxxx_add_final_score_fields_to_applications.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Bonificacion por edad (Ley 31533)
            $table->decimal('age_bonus', 5, 2)->nullable()
                  ->after('interview_score')
                  ->comment('Bonificacion 10% para menores de 29 anios');

            // Puntaje entrevista con bonus joven
            $table->decimal('interview_score_with_bonus', 5, 2)->nullable()
                  ->after('age_bonus')
                  ->comment('interview_score + age_bonus');

            // Puntaje base (CV + Entrevista con bonus)
            $table->decimal('base_score', 5, 2)->nullable()
                  ->after('interview_score_with_bonus')
                  ->comment('curriculum_score + interview_score_with_bonus');

            // Experiencia sector publico
            $table->integer('public_sector_years')->nullable()
                  ->after('base_score')
                  ->comment('Anios de experiencia en sector publico');

            $table->decimal('public_sector_bonus', 5, 2)->nullable()
                  ->after('public_sector_years')
                  ->comment('Bonificacion por exp. sector publico (max 3 pts)');

            // Total bonificaciones especiales
            $table->decimal('special_bonus_total', 5, 2)->nullable()
                  ->after('public_sector_bonus')
                  ->comment('Suma de bonificaciones por discapacidad, militar, etc');

            // Resultado de seleccion
            $table->enum('selection_result', ['GANADOR', 'ACCESITARIO', 'NO_SELECCIONADO', 'NO_APTO'])
                  ->nullable()
                  ->after('final_ranking')
                  ->comment('Resultado del proceso de seleccion');

            // Orden de accesitario
            $table->integer('accesitario_order')->nullable()
                  ->after('selection_result')
                  ->comment('Orden de prioridad si es accesitario (1, 2, 3...)');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'age_bonus',
                'interview_score_with_bonus',
                'base_score',
                'public_sector_years',
                'public_sector_bonus',
                'special_bonus_total',
                'selection_result',
                'accesitario_order',
            ]);
        });
    }
};
```

---

## 10. PROXIMOS PASOS DE IMPLEMENTACION

### Orden Recomendado:

1. [ ] **Crear migracion** de nuevos campos en `applications`
2. [ ] **Implementar BonusCalculationService** (servicio base)
3. [ ] **Implementar InterviewResultProcessingService** + Controlador + Vistas
4. [ ] **Implementar FinalScoreCalculationService** + Controlador + Vistas
5. [ ] **Implementar WinnerAssignmentService** + Controlador + Vistas
6. [ ] **Actualizar ResultExportService** con nuevas columnas
7. [ ] **Agregar rutas** al web.php del modulo Results
8. [ ] **Actualizar dashboard** con enlaces a nuevos procesos
9. [ ] **Probar flujo completo** con datos de prueba
10. [ ] **Ajustar permisos** de acceso

---

## 11. RESUMEN DE BONIFICACIONES

| Tipo | Base de Calculo | Porcentaje/Puntos | Ley |
|------|-----------------|-------------------|-----|
| Menor de 29 anios | Puntaje Entrevista | 10% | Ley 31533 Art. 3.1 |
| Exp. Sector Publico | Fijo | +1 pt/anio (max 3) | Ley 31533 Art. 3.2 |
| Discapacidad | Puntaje Base | 15% | Ley 29973 |
| Licenciado FF.AA. | Puntaje Base | 10% | Ley 29248 |
| Deportista Nacional | Puntaje Base | 10% | Ley 27674 |
| Deportista Internacional | Puntaje Base | 15% | Ley 27674 |
| Victima Terrorismo | Puntaje Base | 10% | Ley 27277 |

**IMPORTANTE:** Todas las bonificaciones son **ACUMULABLES**. El puntaje final **PUEDE SUPERAR 100 puntos**.

---

**Documento creado por:** Sistema CAS
**Version:** 1.0
**Referencia Legal:** Ley 31533 (Bonificacion Jovenes y Exp. Publica)
