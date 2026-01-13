<?php

namespace Modules\Application\Repositories;

use Modules\Application\Entities\Application;
use Modules\Application\Repositories\Contracts\ApplicationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApplicationRepository implements ApplicationRepositoryInterface
{
    public function __construct(
        protected Application $model
    ) {}

    public function find(string $id): ?Application
    {
        return $this->model
            ->with([
                'jobProfile',  // ← ACTUALIZADO: relación directa
                'assignedVacancy',  // ← ACTUALIZADO: vacante asignada si existe
                'applicant',
                'academics',
                'experiences',
                'trainings',
                'specialConditions',
                'professionalRegistrations',
                'knowledge'
            ])
            ->find($id);
    }

    public function findByCode(string $code): ?Application
    {
        return $this->model
            ->with([
                'jobProfile',  // ← ACTUALIZADO
                'assignedVacancy',  // ← ACTUALIZADO
                'applicant',
                'academics',
                'experiences',
                'trainings',
                'specialConditions',
                'professionalRegistrations',
                'knowledge'
            ])
            ->where('code', $code)
            ->first();
    }

    public function create(array $data): Application
    {
        return $this->model->create($data);
    }

    public function update(Application $application, array $data): Application
    {
        $application->update($data);
        return $application->fresh();
    }

    public function delete(Application $application): bool
    {
        return $application->delete();
    }

    /**
     * Obtener postulaciones por perfil de trabajo
     */
    public function getByJobProfile(string $jobProfileId): Collection
    {
        return $this->model
            ->with(['applicant', 'academics', 'experiences', 'assignedVacancy'])
            ->where('job_profile_id', $jobProfileId)
            ->orderBy('application_date', 'desc')
            ->get();
    }

    /**
     * @deprecated Use getByJobProfile() instead. Maintained for backwards compatibility.
     */
    public function getByVacancy(string $vacancyId): Collection
    {
        return $this->model
            ->with(['applicant', 'academics', 'experiences'])
            ->where('assigned_vacancy_id', $vacancyId)  // ← ACTUALIZADO
            ->whereNotNull('assigned_vacancy_id')  // Solo ganadores con vacante asignada
            ->orderBy('application_date', 'desc')
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model
            ->with(['jobProfile', 'assignedVacancy', 'applicant'])  // ← ACTUALIZADO
            ->where('status', $status)
            ->orderBy('application_date', 'desc')
            ->get();
    }

    public function getEligible(): Collection
    {
        return $this->getByStatus('APTO');
    }

    public function getNotEligible(): Collection
    {
        return $this->getByStatus('NO_APTO');
    }

    public function getByApplicant(string $applicantId): Collection
    {
        return $this->model
            ->with(['jobProfile', 'assignedVacancy'])  // ← ACTUALIZADO
            ->where('applicant_id', $applicantId)
            ->orderBy('application_date', 'desc')
            ->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['jobProfile', 'assignedVacancy', 'applicant']);  // ← ACTUALIZADO

        // Filtro por estado
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filtro por perfil de trabajo
        if (isset($filters['job_profile_id'])) {
            $query->where('job_profile_id', $filters['job_profile_id']);
        }

        // Filtro por vacante asignada (backwards compatibility)
        if (isset($filters['vacancy_id'])) {
            $query->where('assigned_vacancy_id', $filters['vacancy_id']);
        }

        // Filtro por DNI
        if (isset($filters['dni'])) {
            $query->where('dni', 'LIKE', "%{$filters['dni']}%");
        }

        // Filtro por nombre
        if (isset($filters['name'])) {
            $query->where('full_name', 'LIKE', "%{$filters['name']}%");
        }

        // Filtro por elegibilidad
        if (isset($filters['is_eligible'])) {
            $query->where('is_eligible', $filters['is_eligible']);
        }

        // Filtro por rango de fechas
        if (isset($filters['date_from'])) {
            $query->where('application_date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('application_date', '<=', $filters['date_to']);
        }

        // Ordenamiento
        $sortBy = $filters['sort_by'] ?? 'application_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Verificar si un postulante ya aplicó a un perfil de trabajo
     * @param string $applicantId ID del postulante
     * @param string $jobProfileId ID del perfil de trabajo
     */
    public function hasApplied(string $applicantId, string $jobProfileId): bool
    {
        return $this->model
            ->where('applicant_id', $applicantId)
            ->where('job_profile_id', $jobProfileId)  // ← ACTUALIZADO
            ->whereNotIn('status', [
                \Modules\Application\Enums\ApplicationStatus::WITHDRAWN,
                \Modules\Application\Enums\ApplicationStatus::REJECTED
            ])
            ->exists();
    }

    public function countByStatus(string $status): int
    {
        return $this->model
            ->where('status', $status)
            ->count();
    }

    /**
     * Obtener ranking de postulaciones por perfil de trabajo
     */
    public function getRankingByJobProfile(string $jobProfileId): Collection
    {
        return $this->model
            ->with(['applicant', 'assignedVacancy'])
            ->where('job_profile_id', $jobProfileId)
            ->whereNotNull('final_score')
            ->orderBy('final_ranking', 'asc')
            ->orderBy('final_score', 'desc')
            ->get();
    }

    /**
     * @deprecated Use getRankingByJobProfile() instead
     */
    public function getRankingByVacancy(string $vacancyId): Collection
    {
        return $this->model
            ->with(['applicant'])
            ->where('assigned_vacancy_id', $vacancyId)  // ← ACTUALIZADO
            ->whereNotNull('final_score')
            ->orderBy('final_score', 'desc')
            ->orderBy('final_ranking', 'asc')
            ->get();
    }

    public function searchByDni(string $dni): Collection
    {
        return $this->model
            ->with(['vacancy', 'applicant'])
            ->where('dni', 'LIKE', "%{$dni}%")
            ->orderBy('application_date', 'desc')
            ->get();
    }
}
