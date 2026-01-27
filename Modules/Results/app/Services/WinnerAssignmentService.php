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
