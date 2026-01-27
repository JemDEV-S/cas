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
                    $willPass = false;
                    if ($score >= self::MIN_PASSING_SCORE) {
                        // Mantiene APTO (o vuelve a APTO si fue re-procesado)
                        if ($app->status === ApplicationStatus::NOT_ELIGIBLE) {
                            $app->status = ApplicationStatus::ELIGIBLE;
                            $app->is_eligible = true;
                            $app->ineligibility_reason = null;
                        }
                        $willPass = true;
                    } else {
                        // Pasa a NO_APTO
                        $app->status = ApplicationStatus::NOT_ELIGIBLE;
                        $app->is_eligible = false;
                        $app->ineligibility_reason = $evaluation->general_comments
                            ?: "Puntaje curricular ({$score}/50) menor al minimo requerido (35)";
                        $willPass = false;
                    }

                    $app->save();

                    // Registrar en historial
                    $this->logProcessing($app, $oldScore, $score, $oldStatus, $app->status);

                    // Incrementar contadores DESPUÉS de que todo sea exitoso
                    $processed++;
                    if ($willPass) {
                        $passed++;
                    } else {
                        $failed++;
                    }

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
            'event_type' => 'CV_RESULT_PROCESSED',
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
