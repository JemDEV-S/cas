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
                'military_bonus' => $bonuses['military_bonus'],
                'interview_score_with_bonus' => $bonuses['interview_score_with_bonus'],
                'base_score' => $bonuses['base_score'],
                'public_sector_years' => $bonuses['public_sector_years'],
                'public_sector_bonus' => $bonuses['public_sector_bonus'],
                'subtotal' => $bonuses['subtotal'],
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

                    // NOTA: age_bonus, military_bonus e interview_score_with_bonus ya fueron
                    // guardados por InterviewResultProcessingService, no necesitan actualizarse aquí

                    // Actualizar campos calculados en esta fase
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
