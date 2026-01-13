<?php

namespace Modules\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Application\Entities\Application;
use Modules\JobProfile\Entities\JobProfile;
use Modules\JobProfile\Entities\JobProfileVacancy;

/**
 * Service: VacancyAssignmentService
 *
 * Maneja la asignación de vacantes a postulaciones ganadoras.
 * Asigna vacantes basándose en el ranking final de los postulantes.
 */
class VacancyAssignmentService
{
    /**
     * Asignar vacantes automáticamente según ranking
     *
     * @param string $jobProfileId ID del perfil de trabajo
     * @return array Lista de asignaciones realizadas
     * @throws \Exception Si no hay vacantes disponibles o postulantes elegibles
     */
    public function assignVacanciesByRanking(string $jobProfileId): array
    {
        return DB::transaction(function () use ($jobProfileId) {
            $jobProfile = JobProfile::findOrFail($jobProfileId);

            // 1. Obtener vacantes disponibles
            $availableVacancies = $jobProfile->vacancies()
                ->where('status', 'available')
                ->orderBy('vacancy_number')
                ->get();

            if ($availableVacancies->isEmpty()) {
                throw new \Exception('No hay vacantes disponibles');
            }

            // 2. Obtener ganadores (top N según ranking)
            $winners = Application::where('job_profile_id', $jobProfileId)
                ->where('is_eligible', true)
                ->whereNull('assigned_vacancy_id')
                ->orderBy('final_ranking', 'asc')
                ->limit($availableVacancies->count())
                ->get();

            if ($winners->isEmpty()) {
                throw new \Exception('No hay postulantes elegibles');
            }

            $assignments = [];

            // 3. Asignar vacantes
            foreach ($winners as $index => $winner) {
                if (!isset($availableVacancies[$index])) {
                    break;
                }

                $vacancy = $availableVacancies[$index];

                // Actualizar Application
                $winner->assigned_vacancy_id = $vacancy->id;
                $winner->status = 'GANADOR';
                $winner->save();

                // Actualizar Vacancy
                $vacancy->status = 'filled';
                $vacancy->assigned_application_id = $winner->id;
                $vacancy->save();

                // Registrar en historial
                $winner->history()->create([
                    'action' => 'vacancy_assigned',
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                    'details' => [
                        'vacancy_code' => $vacancy->code,
                        'ranking' => $winner->final_ranking,
                        'score' => $winner->final_score,
                    ],
                ]);

                $assignments[] = [
                    'application' => $winner->fresh(),
                    'vacancy' => $vacancy->fresh(),
                ];
            }

            return $assignments;
        });
    }

    /**
     * Reasignar vacante (si ganador renuncia o es descalificado)
     *
     * @param string $vacancyId ID de la vacante
     * @param string $newApplicationId ID de la nueva postulación ganadora
     * @return array Datos de la reasignación
     * @throws \Exception Si la reasignación falla
     */
    public function reassignVacancy(string $vacancyId, string $newApplicationId): array
    {
        return DB::transaction(function () use ($vacancyId, $newApplicationId) {
            $vacancy = JobProfileVacancy::findOrFail($vacancyId);
            $newWinner = Application::findOrFail($newApplicationId);

            // Liberar vacante del anterior ganador
            if ($vacancy->assigned_application_id) {
                $previousWinner = Application::find($vacancy->assigned_application_id);
                if ($previousWinner) {
                    $previousWinner->assigned_vacancy_id = null;
                    $previousWinner->status = 'APTO';
                    $previousWinner->save();

                    // Registrar en historial
                    $previousWinner->history()->create([
                        'action' => 'vacancy_released',
                        'performed_by' => auth()->id(),
                        'performed_at' => now(),
                        'details' => [
                            'vacancy_code' => $vacancy->code,
                            'reason' => 'Reasignación de vacante',
                        ],
                    ]);
                }
            }

            // Asignar al nuevo ganador
            $newWinner->assigned_vacancy_id = $vacancy->id;
            $newWinner->status = 'GANADOR';
            $newWinner->save();

            $vacancy->assigned_application_id = $newWinner->id;
            $vacancy->status = 'filled';
            $vacancy->save();

            // Registrar en historial
            $newWinner->history()->create([
                'action' => 'vacancy_assigned',
                'performed_by' => auth()->id(),
                'performed_at' => now(),
                'details' => [
                    'vacancy_code' => $vacancy->code,
                    'ranking' => $newWinner->final_ranking,
                    'score' => $newWinner->final_score,
                    'reason' => 'Reasignación',
                ],
            ]);

            return [
                'vacancy' => $vacancy->fresh(),
                'new_winner' => $newWinner->fresh(),
                'previous_winner' => $previousWinner ?? null,
            ];
        });
    }

    /**
     * Liberar vacante (declarar desierta o renuncia de ganador)
     *
     * @param string $vacancyId ID de la vacante
     * @param string $reason Motivo de liberación
     * @return JobProfileVacancy Vacante actualizada
     */
    public function releaseVacancy(string $vacancyId, string $reason): JobProfileVacancy
    {
        return DB::transaction(function () use ($vacancyId, $reason) {
            $vacancy = JobProfileVacancy::findOrFail($vacancyId);

            // Liberar postulación si hay una asignada
            if ($vacancy->assigned_application_id) {
                $application = Application::find($vacancy->assigned_application_id);
                if ($application) {
                    $application->assigned_vacancy_id = null;
                    $application->status = 'APTO';
                    $application->save();

                    // Registrar en historial
                    $application->history()->create([
                        'action' => 'vacancy_released',
                        'performed_by' => auth()->id(),
                        'performed_at' => now(),
                        'details' => [
                            'vacancy_code' => $vacancy->code,
                            'reason' => $reason,
                        ],
                    ]);
                }
            }

            // Marcar vacante como disponible
            $vacancy->status = 'available';
            $vacancy->assigned_application_id = null;
            $vacancy->save();

            return $vacancy->fresh();
        });
    }

    /**
     * Obtener siguiente candidato elegible para una vacante liberada
     *
     * @param string $jobProfileId ID del perfil de trabajo
     * @return Application|null Siguiente candidato elegible o null si no hay
     */
    public function getNextEligibleCandidate(string $jobProfileId): ?Application
    {
        return Application::where('job_profile_id', $jobProfileId)
            ->where('is_eligible', true)
            ->whereNull('assigned_vacancy_id')
            ->orderBy('final_ranking', 'asc')
            ->first();
    }

    /**
     * Asignar una vacante específica a una postulación específica
     *
     * @param string $vacancyId ID de la vacante
     * @param string $applicationId ID de la postulación
     * @return array Datos de la asignación
     */
    public function assignSpecificVacancy(string $vacancyId, string $applicationId): array
    {
        return DB::transaction(function () use ($vacancyId, $applicationId) {
            $vacancy = JobProfileVacancy::findOrFail($vacancyId);
            $application = Application::findOrFail($applicationId);

            // Validaciones
            if ($vacancy->status !== 'available') {
                throw new \Exception('La vacante no está disponible');
            }

            if (!$application->is_eligible) {
                throw new \Exception('La postulación no es elegible');
            }

            if ($application->assigned_vacancy_id) {
                throw new \Exception('La postulación ya tiene una vacante asignada');
            }

            // Asignar
            $application->assigned_vacancy_id = $vacancy->id;
            $application->status = 'GANADOR';
            $application->save();

            $vacancy->status = 'filled';
            $vacancy->assigned_application_id = $application->id;
            $vacancy->save();

            // Registrar en historial
            $application->history()->create([
                'action' => 'vacancy_assigned',
                'performed_by' => auth()->id(),
                'performed_at' => now(),
                'details' => [
                    'vacancy_code' => $vacancy->code,
                    'ranking' => $application->final_ranking,
                    'score' => $application->final_score,
                    'assignment_type' => 'manual',
                ],
            ]);

            return [
                'vacancy' => $vacancy->fresh(),
                'application' => $application->fresh(),
            ];
        });
    }
}
