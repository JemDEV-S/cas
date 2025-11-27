<?php

namespace Modules\JobProfile\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Exceptions\BusinessRuleException;
use Modules\JobProfile\Entities\JobProfile;
use Modules\JobProfile\Entities\JobProfileVacancy;

class VacancyService
{
    /**
     * Genera vacantes automáticamente al aprobar un perfil
     */
    public function generateVacancies(JobProfile $jobProfile): array
    {
        return DB::transaction(function () use ($jobProfile) {
            if (!$jobProfile->isApproved()) {
                throw new BusinessRuleException('Solo se pueden generar vacantes para perfiles aprobados.');
            }

            // Verificar si ya tiene vacantes generadas
            $existingCount = $jobProfile->vacancies()->count();
            if ($existingCount > 0) {
                throw new BusinessRuleException('Este perfil ya tiene vacantes generadas.');
            }

            $vacancies = [];
            $totalVacancies = $jobProfile->total_vacancies ?? 1;

            for ($i = 1; $i <= $totalVacancies; $i++) {
                $vacancy = JobProfileVacancy::create([
                    'job_profile_id' => $jobProfile->id,
                    'vacancy_number' => $i,
                    'code' => $this->generateVacancyCode($jobProfile, $i),
                    'status' => 'available',
                ]);

                $vacancies[] = $vacancy;
            }

            return $vacancies;
        });
    }

    /**
     * Genera el código único de la vacante
     * Formato: CONV-2025-001-01-V01
     */
    protected function generateVacancyCode(JobProfile $jobProfile, int $vacancyNumber): string
    {
        $jobProfileCode = $jobProfile->code;
        $vacancySuffix = 'V' . str_pad($vacancyNumber, 2, '0', STR_PAD_LEFT);

        return "{$jobProfileCode}-{$vacancySuffix}";
    }

    /**
     * Asigna una postulación a una vacante
     */
    public function assignVacancy(string $vacancyId, string $applicationId): JobProfileVacancy
    {
        return DB::transaction(function () use ($vacancyId, $applicationId) {
            $vacancy = JobProfileVacancy::findOrFail($vacancyId);
            $vacancy->assignTo($applicationId);

            return $vacancy->fresh();
        });
    }

    /**
     * Declara una vacante como desierta
     */
    public function declareVacant(string $vacancyId, string $reason): JobProfileVacancy
    {
        return DB::transaction(function () use ($vacancyId, $reason) {
            $vacancy = JobProfileVacancy::findOrFail($vacancyId);
            $vacancy->declareVacant($reason);

            return $vacancy->fresh();
        });
    }

    /**
     * Obtiene estadísticas de vacantes
     */
    public function getVacancyStatistics(string $jobProfileId): array
    {
        $vacancies = JobProfileVacancy::where('job_profile_id', $jobProfileId)->get();

        return [
            'total' => $vacancies->count(),
            'available' => $vacancies->where('status', 'available')->count(),
            'in_process' => $vacancies->where('status', 'in_process')->count(),
            'filled' => $vacancies->where('status', 'filled')->count(),
            'vacant' => $vacancies->where('status', 'vacant')->count(),
        ];
    }
}
