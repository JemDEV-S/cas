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
