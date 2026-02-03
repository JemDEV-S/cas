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
            $militaryBonus = $this->bonusService->calculateMilitaryBonus($app, $score);

            $item = [
                'application' => $app,
                'evaluation' => $evaluation,
                'score_raw' => $score,
                'age' => $age,
                'age_bonus' => round($ageBonus, 2),
                'military_bonus' => round($militaryBonus, 2),
                'score_with_bonus' => round($score + $ageBonus + $militaryBonus, 2),
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
                    $oldScore = $app->interview_score;
                    $oldStatus = $app->status;

                    $ageBonus = $this->bonusService->calculateAgeBonus($app, $score);
                    $militaryBonus = $this->bonusService->calculateMilitaryBonus($app, $score);

                    // Actualizar puntajes (incluyendo ambas bonificaciones sobre entrevista RAW)
                    $app->interview_score = $score;
                    $app->age_bonus = $ageBonus;
                    $app->military_bonus = $militaryBonus;
                    $app->interview_score_with_bonus = $score + $ageBonus + $militaryBonus;

                    // Determinar estado
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
                        // No aprobo entrevista
                        $app->status = ApplicationStatus::NOT_ELIGIBLE;
                        $app->is_eligible = false;
                        $app->ineligibility_reason = $evaluation->general_comments
                            ?: "Puntaje entrevista ({$score}/50) menor al minimo (35)";
                        $willPass = false;
                    }

                    $app->save();
                    $this->logProcessing($app, $oldScore, $score, $oldStatus, $app->status);

                    // Incrementar contadores DESPUÉS de que todo sea exitoso
                    $processed++;
                    if ($willPass) {
                        $passed++;
                    } else {
                        $failed++;
                    }

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
            ->whereNotNull('curriculum_score')
            ->where('curriculum_score', '>=', 35)
            ->where(function($q) {
                $q->where(function($q1) {
                      // Incluir ELIGIBLE que pasaron CV
                      $q1->where('status', ApplicationStatus::ELIGIBLE)
                         ->where('is_eligible', true);
                  })
                  ->orWhere(function($q2) {
                      // Incluir NO_APTO que ya fueron procesados con entrevista (para re-proceso)
                      $q2->where('status', ApplicationStatus::NOT_ELIGIBLE)
                         ->whereNotNull('interview_score');
                  })
                  ->orWhere(function($q3) {
                      // Incluir APPROVED (ganadores que necesitan re-proceso por reclamos)
                      $q3->where('status', ApplicationStatus::APPROVED)
                         ->whereNotNull('interview_score');
                  });
            })
            ->with(['jobProfile.positionCode', 'applicant', 'specialConditions'])
            ->orderBy('full_name')
            ->get();
    }

    private function getInterviewPhase()
    {
        return \Modules\JobPosting\Entities\ProcessPhase::where('code', 'PHASE_08_INTERVIEW')->first();
    }

    private function logProcessing($application, $oldScore, $newScore, $oldStatus, $newStatus): void
    {
        $description = "Procesamiento de resultados entrevista: ";

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
            'event_type' => 'INTERVIEW_RESULT_PROCESSED',
            'description' => $description,
            'performed_by' => auth()->id(),
            'performed_at' => now(),
            'metadata' => [
                'old_score' => $oldScore,
                'new_score' => $newScore,
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
                'age_bonus' => $application->age_bonus,
                'military_bonus' => $application->military_bonus,
                'interview_score_with_bonus' => $application->interview_score_with_bonus,
                'min_required' => self::MIN_PASSING_SCORE,
            ],
        ]);
    }
}
