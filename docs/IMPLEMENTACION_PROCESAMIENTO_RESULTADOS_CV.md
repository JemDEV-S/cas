# Documentacion de Implementacion: Procesamiento de Resultados de Evaluacion Curricular

## Sistema CAS - Municipalidad (Peru)

**Fecha:** 2026-01-26
**Modulo:** Results / Evaluation
**Fase del Proceso:** FASE 6 → FASE 7 (Evaluacion Curricular → Publicacion de Resultados)

---

## 1. CONTEXTO Y OBJETIVO

### 1.1 Situacion Actual
- Los jurados ya estan evaluando CVs de postulantes APTOS
- Cada postulacion tiene UN evaluador asignado (relacion 1:1)
- Las evaluaciones tienen puntaje total (`Evaluation.total_score`) de 0 a 50
- El campo `Application.curriculum_score` esta vacio

### 1.2 Objetivo
Implementar una interfaz en el modulo Results que:
1. Procese las evaluaciones completadas de CV
2. Transfiera el puntaje a `Application.curriculum_score`
3. Aplique la logica de puntaje minimo (35 puntos)
4. Cambie el estado de postulaciones segun el puntaje:
   - **>= 35 puntos:** Mantiene estado `APTO` (continua al siguiente paso)
   - **< 35 puntos:** Cambia a estado `NO_APTO` (descalificado por CV)
5. Permita previsualizacion (dry-run) antes de confirmar
6. Sea reversible (re-procesable si hay correcciones)

---

## 2. REGLAS DE NEGOCIO

### 2.1 Calculo de Puntaje
| Regla | Valor |
|-------|-------|
| Puntaje maximo | 50 puntos (fijo) |
| Puntaje minimo para continuar | 35 puntos |
| Evaluadores por postulacion | 1 (un solo jurado) |
| Fuente del puntaje | `Evaluation.total_score` |
| Destino del puntaje | `Application.curriculum_score` |

### 2.2 Cambio de Estados

```
Estado Actual: APTO (ELIGIBLE)
                    |
        ┌───────────┴───────────┐
        ▼                       ▼
   >= 35 puntos            < 35 puntos
        |                       |
        ▼                       ▼
  Mantiene APTO          Cambia a NO_APTO
  (continua proceso)     (ineligibility_reason =
                          comentarios de evaluacion)
```

### 2.3 Validaciones
- Solo procesar postulaciones con estado `APTO` (ELIGIBLE)
- Solo considerar evaluaciones con status `SUBMITTED` o `MODIFIED`
- Las evaluaciones pendientes (`ASSIGNED`, `IN_PROGRESS`) se ignoran (permitir parcial)
- Debe existir CV subido para la postulacion
- Mostrar advertencia si hay evaluaciones pendientes

### 2.4 Procesamiento Reversible
- Permitir re-procesar cuando hay modificaciones en evaluaciones
- Actualizar `curriculum_score` si cambio el puntaje
- Revertir estado si el nuevo puntaje cruza el umbral (35)
- Registrar en historial cada procesamiento

---

## 3. DISENO DE LA SOLUCION

### 3.1 Nueva Ruta y Controlador

**Ruta:** `GET /admin/postings/{posting}/results/cv-processing`
**Nombre:** `admin.results.cv-processing`

```php
// Modules/Results/routes/web.php
Route::get('postings/{posting}/results/cv-processing',
    [CvResultProcessingController::class, 'index'])
    ->name('cv-processing');

Route::post('postings/{posting}/results/cv-processing/preview',
    [CvResultProcessingController::class, 'preview'])
    ->name('cv-processing.preview');

Route::post('postings/{posting}/results/cv-processing/execute',
    [CvResultProcessingController::class, 'execute'])
    ->name('cv-processing.execute');
```

### 3.2 Nuevo Controlador

**Archivo:** `Modules/Results/app/Http/Controllers/Admin/CvResultProcessingController.php`

```php
<?php

namespace Modules\Results\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Results\Services\CvResultProcessingService;
use Modules\JobPosting\Entities\JobPosting;

class CvResultProcessingController extends Controller
{
    public function __construct(
        private CvResultProcessingService $processingService
    ) {}

    /**
     * Vista principal de procesamiento
     */
    public function index(JobPosting $posting)
    {
        $summary = $this->processingService->getSummary($posting);

        return view('results::admin.cv-processing.index', compact('posting', 'summary'));
    }

    /**
     * Previsualizacion (dry-run)
     */
    public function preview(Request $request, JobPosting $posting)
    {
        $preview = $this->processingService->preview($posting);

        return view('results::admin.cv-processing.preview', compact('posting', 'preview'));
    }

    /**
     * Ejecutar procesamiento
     */
    public function execute(Request $request, JobPosting $posting)
    {
        try {
            $result = $this->processingService->execute($posting);

            return redirect()
                ->route('admin.results.cv-processing', $posting)
                ->with('success', "Procesamiento completado: {$result['processed']} postulaciones actualizadas.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error en procesamiento: ' . $e->getMessage());
        }
    }
}
```

### 3.3 Nuevo Servicio

**Archivo:** `Modules/Results/app/Services/CvResultProcessingService.php`

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

class CvResultProcessingService
{
    const MIN_PASSING_SCORE = 35;
    const MAX_SCORE = 50;

    /**
     * Obtener resumen del estado actual
     */
    public function getSummary(JobPosting $posting): array
    {
        // Postulaciones APTAS de esta convocatoria
        $applications = $this->getEligibleApplications($posting);

        // Evaluaciones de CV (fase 6)
        $cvPhase = $this->getCvEvaluationPhase();

        $withEvaluation = 0;
        $withoutEvaluation = 0;
        $evaluationsSubmitted = 0;
        $evaluationsPending = 0;
        $alreadyProcessed = 0;

        foreach ($applications as $app) {
            $evaluation = Evaluation::where('application_id', $app->id)
                ->where('phase_id', $cvPhase?->id)
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

            if ($app->curriculum_score !== null) {
                $alreadyProcessed++;
            }
        }

        return [
            'total_eligible' => $applications->count(),
            'with_evaluation' => $withEvaluation,
            'without_evaluation' => $withoutEvaluation,
            'evaluations_submitted' => $evaluationsSubmitted,
            'evaluations_pending' => $evaluationsPending,
            'already_processed' => $alreadyProcessed,
            'ready_to_process' => $evaluationsSubmitted,
            'cv_phase' => $cvPhase,
        ];
    }

    /**
     * Previsualizacion (dry-run) del procesamiento
     */
    public function preview(JobPosting $posting): array
    {
        $applications = $this->getEligibleApplications($posting);
        $cvPhase = $this->getCvEvaluationPhase();

        $preview = [
            'will_pass' => [],      // >= 35, mantienen APTO
            'will_fail' => [],      // < 35, pasan a NO_APTO
            'no_evaluation' => [],  // Sin evaluacion completada
            'already_not_eligible' => [], // Ya son NO_APTO
        ];

        foreach ($applications as $app) {
            $evaluation = Evaluation::where('application_id', $app->id)
                ->where('phase_id', $cvPhase?->id)
                ->whereIn('status', [
                    EvaluationStatusEnum::SUBMITTED,
                    EvaluationStatusEnum::MODIFIED,
                ])
                ->first();

            if (!$evaluation) {
                $preview['no_evaluation'][] = [
                    'application' => $app,
                    'reason' => 'Sin evaluacion completada',
                ];
                continue;
            }

            $score = $evaluation->total_score ?? 0;
            $currentScore = $app->curriculum_score;
            $isReprocess = $currentScore !== null;

            $item = [
                'application' => $app,
                'evaluation' => $evaluation,
                'score' => $score,
                'current_score' => $currentScore,
                'is_reprocess' => $isReprocess,
                'evaluator' => $evaluation->evaluator?->name ?? 'N/A',
                'comments' => $evaluation->general_comments,
            ];

            if ($score >= self::MIN_PASSING_SCORE) {
                $item['new_status'] = ApplicationStatus::ELIGIBLE;
                $item['status_label'] = 'Mantiene APTO';
                $preview['will_pass'][] = $item;
            } else {
                $item['new_status'] = ApplicationStatus::NOT_ELIGIBLE;
                $item['status_label'] = 'Cambia a NO_APTO';
                $item['reason'] = "Puntaje curricular ({$score}/50) menor al minimo requerido (35)";
                $preview['will_fail'][] = $item;
            }
        }

        // Ordenar por puntaje
        usort($preview['will_pass'], fn($a, $b) => $b['score'] <=> $a['score']);
        usort($preview['will_fail'], fn($a, $b) => $b['score'] <=> $a['score']);

        $preview['summary'] = [
            'total_to_process' => count($preview['will_pass']) + count($preview['will_fail']),
            'will_pass_count' => count($preview['will_pass']),
            'will_fail_count' => count($preview['will_fail']),
            'no_evaluation_count' => count($preview['no_evaluation']),
            'min_score' => self::MIN_PASSING_SCORE,
            'max_score' => self::MAX_SCORE,
        ];

        return $preview;
    }

    /**
     * Ejecutar procesamiento real
     */
    public function execute(JobPosting $posting): array
    {
        return DB::transaction(function () use ($posting) {
            $applications = $this->getEligibleApplications($posting);
            $cvPhase = $this->getCvEvaluationPhase();

            $processed = 0;
            $passed = 0;
            $failed = 0;
            $skipped = 0;
            $errors = [];

            foreach ($applications as $app) {
                try {
                    $evaluation = Evaluation::where('application_id', $app->id)
                        ->where('phase_id', $cvPhase?->id)
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
                    $oldScore = $app->curriculum_score;
                    $oldStatus = $app->status;

                    // Actualizar puntaje
                    $app->curriculum_score = $score;

                    // Determinar nuevo estado
                    if ($score >= self::MIN_PASSING_SCORE) {
                        // Mantiene APTO (o vuelve a APTO si fue re-procesado)
                        if ($app->status === ApplicationStatus::NOT_ELIGIBLE) {
                            $app->status = ApplicationStatus::ELIGIBLE;
                            $app->is_eligible = true;
                            $app->ineligibility_reason = null;
                        }
                        $passed++;
                    } else {
                        // Pasa a NO_APTO
                        $app->status = ApplicationStatus::NOT_ELIGIBLE;
                        $app->is_eligible = false;
                        $app->ineligibility_reason = $evaluation->general_comments
                            ?: "Puntaje curricular ({$score}/50) menor al minimo requerido (35)";
                        $failed++;
                    }

                    $app->save();

                    // Registrar en historial
                    $this->logProcessing($app, $oldScore, $score, $oldStatus, $app->status);

                    $processed++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'application_id' => $app->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            Log::info('Procesamiento de resultados CV ejecutado', [
                'job_posting_id' => $posting->id,
                'processed' => $processed,
                'passed' => $passed,
                'failed' => $failed,
                'skipped' => $skipped,
                'errors' => count($errors),
            ]);

            return [
                'processed' => $processed,
                'passed' => $passed,
                'failed' => $failed,
                'skipped' => $skipped,
                'errors' => $errors,
            ];
        });
    }

    /**
     * Obtener postulaciones elegibles de la convocatoria
     */
    private function getEligibleApplications(JobPosting $posting)
    {
        return Application::whereHas('jobProfile', fn($q) =>
                $q->where('job_posting_id', $posting->id)
            )
            ->where(function($q) {
                $q->where('status', ApplicationStatus::ELIGIBLE)
                  ->orWhere(function($q2) {
                      // Incluir NO_APTO que ya fueron procesados (para re-proceso)
                      $q2->where('status', ApplicationStatus::NOT_ELIGIBLE)
                         ->whereNotNull('curriculum_score');
                  });
            })
            ->with(['jobProfile.positionCode', 'jobProfile.requestingUnit', 'applicant'])
            ->orderBy('full_name')
            ->get();
    }

    /**
     * Obtener la fase de evaluacion curricular (Fase 6)
     */
    private function getCvEvaluationPhase()
    {
        return \Modules\JobPosting\Entities\ProcessPhase::where('code', 'PHASE_06_CV_EVALUATION')
            ->first();
    }

    /**
     * Registrar en historial de postulacion
     */
    private function logProcessing($application, $oldScore, $newScore, $oldStatus, $newStatus): void
    {
        $description = "Procesamiento de resultados CV: ";

        if ($oldScore === null) {
            $description .= "Puntaje asignado: {$newScore}/50. ";
        } else {
            $description .= "Puntaje actualizado: {$oldScore} -> {$newScore}/50. ";
        }

        if ($oldStatus !== $newStatus) {
            $description .= "Estado: {$oldStatus->label()} -> {$newStatus->label()}";
        } else {
            $description .= "Estado sin cambios: {$newStatus->label()}";
        }

        $application->history()->create([
            'action_type' => 'CV_RESULT_PROCESSED',
            'description' => $description,
            'performed_by' => auth()->id(),
            'performed_at' => now(),
            'metadata' => [
                'old_score' => $oldScore,
                'new_score' => $newScore,
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
                'min_required' => self::MIN_PASSING_SCORE,
            ],
        ]);
    }
}
```

### 3.4 Vistas Blade

#### Vista Principal: `index.blade.php`

**Archivo:** `Modules/Results/resources/views/admin/cv-processing/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Procesamiento de Resultados CV</h2>
            <p class="text-gray-500 text-sm">
                Convocatoria: <strong>{{ $posting->code }}</strong>
            </p>
        </div>
        <a href="{{ route('admin.job-postings.show', $posting) }}"
           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tarjetas de Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Postulantes APTOS</div>
            <div class="text-3xl font-bold text-blue-600">{{ $summary['total_eligible'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Evaluaciones Completadas</div>
            <div class="text-3xl font-bold text-green-600">{{ $summary['evaluations_submitted'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Evaluaciones Pendientes</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $summary['evaluations_pending'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Ya Procesados</div>
            <div class="text-3xl font-bold text-purple-600">{{ $summary['already_processed'] }}</div>
        </div>
    </div>

    {{-- Panel de Informacion --}}
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Reglas de Procesamiento</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span>Puntaje maximo: <strong>50 puntos</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-yellow-500 mr-2"></i>
                    <span>Puntaje minimo para continuar: <strong>35 puntos</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-user-check text-blue-500 mr-2"></i>
                    <span>>= 35 puntos: Mantiene estado <strong>APTO</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-user-times text-red-500 mr-2"></i>
                    <span>< 35 puntos: Cambia a <strong>NO APTO</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Advertencias --}}
    @if($summary['evaluations_pending'] > 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-yellow-400 mr-3 mt-1"></i>
                <div>
                    <p class="font-medium text-yellow-800">Hay evaluaciones pendientes</p>
                    <p class="text-sm text-yellow-700">
                        {{ $summary['evaluations_pending'] }} postulacion(es) aun no han sido evaluadas completamente.
                        El procesamiento solo considerara las evaluaciones completadas.
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if($summary['without_evaluation'] > 0)
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <i class="fas fa-times-circle text-red-400 mr-3 mt-1"></i>
                <div>
                    <p class="font-medium text-red-800">Postulaciones sin evaluador asignado</p>
                    <p class="text-sm text-red-700">
                        {{ $summary['without_evaluation'] }} postulacion(es) no tienen evaluacion asignada.
                        Asigne evaluadores antes de procesar.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Acciones --}}
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Acciones</h3>

            <div class="flex gap-4">
                @if($summary['evaluations_submitted'] > 0)
                    <form action="{{ route('admin.results.cv-processing.preview', $posting) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>
                            Previsualizar Resultados (Dry Run)
                        </button>
                    </form>
                @else
                    <button disabled class="px-6 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                        <i class="fas fa-search mr-2"></i>
                        Previsualizar Resultados
                    </button>
                    <span class="text-sm text-gray-500 self-center">
                        No hay evaluaciones completadas para procesar
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
```

#### Vista de Previsualizacion: `preview.blade.php`

**Archivo:** `Modules/Results/resources/views/admin/cv-processing/preview.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">
                <i class="fas fa-eye text-blue-600 mr-2"></i>
                Previsualizacion de Resultados (Dry Run)
            </h2>
            <p class="text-gray-500 text-sm">
                Convocatoria: <strong>{{ $posting->code }}</strong> -
                Esta es una simulacion, ningun dato ha sido modificado
            </p>
        </div>
        <a href="{{ route('admin.results.cv-processing', $posting) }}"
           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>

    {{-- Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Total a Procesar</div>
            <div class="text-3xl font-bold text-blue-600">{{ $preview['summary']['total_to_process'] }}</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow-sm border border-green-200 p-4">
            <div class="text-sm text-green-600">Aprobaran (>= 35)</div>
            <div class="text-3xl font-bold text-green-600">{{ $preview['summary']['will_pass_count'] }}</div>
        </div>
        <div class="bg-red-50 rounded-lg shadow-sm border border-red-200 p-4">
            <div class="text-sm text-red-600">Desaprobaran (< 35)</div>
            <div class="text-3xl font-bold text-red-600">{{ $preview['summary']['will_fail_count'] }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow-sm border border-yellow-200 p-4">
            <div class="text-sm text-yellow-600">Sin Evaluacion</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $preview['summary']['no_evaluation_count'] }}</div>
        </div>
    </div>

    {{-- Postulantes que APROBARAN --}}
    @if(count($preview['will_pass']) > 0)
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-4 bg-green-50 border-b border-green-200">
            <h3 class="text-lg font-semibold text-green-800">
                <i class="fas fa-check-circle mr-2"></i>
                Mantendran estado APTO ({{ count($preview['will_pass']) }})
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postulante</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Puesto</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Puntaje</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evaluador</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($preview['will_pass'] as $index => $item)
                    <tr class="hover:bg-green-50">
                        <td class="px-4 py-3 text-sm">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $item['application']->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $item['application']->code }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $item['application']->dni }}</td>
                        <td class="px-4 py-3 text-sm">
                            {{ $item['application']->jobProfile?->positionCode?->code ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                {{ number_format($item['score'], 2) }} / 50
                            </span>
                            @if($item['is_reprocess'])
                                <div class="text-xs text-gray-500 mt-1">
                                    Antes: {{ number_format($item['current_score'], 2) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $item['evaluator'] }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                {{ $item['status_label'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Postulantes que DESAPROBARAN --}}
    @if(count($preview['will_fail']) > 0)
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-4 bg-red-50 border-b border-red-200">
            <h3 class="text-lg font-semibold text-red-800">
                <i class="fas fa-times-circle mr-2"></i>
                Pasaran a NO APTO ({{ count($preview['will_fail']) }})
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postulante</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Puesto</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Puntaje</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evaluador</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($preview['will_fail'] as $index => $item)
                    <tr class="hover:bg-red-50">
                        <td class="px-4 py-3 text-sm">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $item['application']->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $item['application']->code }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $item['application']->dni }}</td>
                        <td class="px-4 py-3 text-sm">
                            {{ $item['application']->jobProfile?->positionCode?->code ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                                {{ number_format($item['score'], 2) }} / 50
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $item['evaluator'] }}</td>
                        <td class="px-4 py-3 text-sm text-red-600">
                            {{ $item['comments'] ?: $item['reason'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Sin evaluacion --}}
    @if(count($preview['no_evaluation']) > 0)
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-4 bg-yellow-50 border-b border-yellow-200">
            <h3 class="text-lg font-semibold text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Sin Evaluacion Completada ({{ count($preview['no_evaluation']) }}) - Seran omitidos
            </h3>
        </div>
        <div class="p-4">
            <ul class="space-y-2">
                @foreach($preview['no_evaluation'] as $item)
                <li class="flex items-center text-sm">
                    <i class="fas fa-minus-circle text-yellow-500 mr-2"></i>
                    <span class="font-medium">{{ $item['application']->full_name }}</span>
                    <span class="text-gray-500 ml-2">({{ $item['application']->dni }})</span>
                    <span class="text-yellow-600 ml-2">- {{ $item['reason'] }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Boton de Ejecutar --}}
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-lg">Confirmar Procesamiento</h3>
                <p class="text-sm text-gray-500">
                    Esta accion actualizara los puntajes y estados de {{ $preview['summary']['total_to_process'] }} postulacion(es).
                </p>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('admin.results.cv-processing', $posting) }}"
                   class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </a>
                <form action="{{ route('admin.results.cv-processing.execute', $posting) }}" method="POST"
                      onsubmit="return confirm('Esta seguro de ejecutar el procesamiento? Esta accion modificara los datos de las postulaciones.')">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-play mr-2"></i>
                        Ejecutar Procesamiento
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## 4. CAMBIOS EN ARCHIVOS EXISTENTES

### 4.1 Agregar Rutas

**Archivo:** `Modules/Results/routes/web.php`

Agregar dentro del grupo `admin.results.`:

```php
// Procesamiento de Resultados CV (Fase 6 -> 7)
Route::get('postings/{posting}/results/cv-processing',
    [CvResultProcessingController::class, 'index'])
    ->name('cv-processing');

Route::post('postings/{posting}/results/cv-processing/preview',
    [CvResultProcessingController::class, 'preview'])
    ->name('cv-processing.preview');

Route::post('postings/{posting}/results/cv-processing/execute',
    [CvResultProcessingController::class, 'execute'])
    ->name('cv-processing.execute');
```

### 4.2 Agregar Enlace en Menu/Dashboard

En la vista de detalle de convocatoria o en el dashboard de resultados, agregar boton:

```blade
<a href="{{ route('admin.results.cv-processing', $posting) }}"
   class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
    <i class="fas fa-calculator mr-2"></i>
    Procesar Resultados CV
</a>
```

---

## 5. ESTRUCTURA DE ARCHIVOS A CREAR

```
Modules/Results/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Admin/
│   │           └── CvResultProcessingController.php  ← NUEVO
│   └── Services/
│       └── CvResultProcessingService.php             ← NUEVO
└── resources/
    └── views/
        └── admin/
            └── cv-processing/                         ← NUEVO DIRECTORIO
                ├── index.blade.php                    ← NUEVO
                └── preview.blade.php                  ← NUEVO
```

---

## 6. FLUJO DE USUARIO

```
1. Admin accede a Convocatoria
      ↓
2. Click en "Procesar Resultados CV"
      ↓
3. Ve resumen: total APTOS, evaluaciones completadas/pendientes
      ↓
4. Click en "Previsualizar (Dry Run)"
      ↓
5. Ve lista de postulantes con puntajes y nuevo estado propuesto
   - Verdes: >= 35, mantienen APTO
   - Rojos: < 35, pasaran a NO_APTO
   - Amarillos: sin evaluacion (omitidos)
      ↓
6. Revisa y confirma
      ↓
7. Click en "Ejecutar Procesamiento"
      ↓
8. Sistema procesa y muestra resultado
      ↓
9. Puede re-procesar si hay cambios
```

---

## 7. CONSIDERACIONES ADICIONALES

### 7.1 Permisos
Agregar permiso `process-cv-results` y asignarlo a roles administrativos.

### 7.2 Auditoria
Cada procesamiento queda registrado en:
- `ApplicationHistory` con action_type = 'CV_RESULT_PROCESSED'
- Logs del sistema

### 7.3 Integracion con Fase 7
Despues de procesar, el admin puede ir a:
- `admin.results.cv-processing.preview` para fase 7 (ya existe)
- Generar documento PDF con firmas digitales

### 7.4 Re-procesamiento
- Si un evaluador modifica una evaluacion (status = MODIFIED)
- Admin puede volver a ejecutar el procesamiento
- El sistema actualiza puntajes y puede revertir estados

---

## 8. PROXIMOS PASOS DE IMPLEMENTACION

1. [ ] Crear `CvResultProcessingService.php`
2. [ ] Crear `CvResultProcessingController.php`
3. [ ] Crear vistas `index.blade.php` y `preview.blade.php`
4. [ ] Agregar rutas en `web.php`
5. [ ] Agregar enlace en dashboard de convocatoria
6. [ ] Probar con datos reales
7. [ ] Ajustar permisos

---

**Documento creado por:** Sistema CAS
**Version:** 1.0
