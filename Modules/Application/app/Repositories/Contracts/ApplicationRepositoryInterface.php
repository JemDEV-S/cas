<?php

namespace Modules\Application\Repositories\Contracts;

use Modules\Application\Entities\Application;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ApplicationRepositoryInterface
{
    /**
     * Encontrar postulación por ID
     */
    public function find(string $id): ?Application;

    /**
     * Encontrar postulación por código
     */
    public function findByCode(string $code): ?Application;

    /**
     * Crear nueva postulación
     */
    public function create(array $data): Application;

    /**
     * Actualizar postulación
     */
    public function update(Application $application, array $data): Application;

    /**
     * Eliminar postulación (soft delete)
     */
    public function delete(Application $application): bool;

    /**
     * Obtener todas las postulaciones de una vacante
     */
    public function getByVacancy(string $vacancyId): Collection;

    /**
     * Obtener postulaciones por estado
     */
    public function getByStatus(string $status): Collection;

    /**
     * Obtener postulaciones aptas
     */
    public function getEligible(): Collection;

    /**
     * Obtener postulaciones no aptas
     */
    public function getNotEligible(): Collection;

    /**
     * Obtener postulaciones de un usuario
     */
    public function getByApplicant(string $applicantId): Collection;

    /**
     * Paginación de postulaciones con filtros
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Verificar si un usuario ya postuló a una vacante
     */
    public function hasApplied(string $applicantId, string $vacancyId): bool;

    /**
     * Contar postulaciones por estado
     */
    public function countByStatus(string $status): int;

    /**
     * Obtener ranking de postulaciones por vacante
     */
    public function getRankingByVacancy(string $vacancyId): Collection;

    /**
     * Buscar postulaciones por DNI
     */
    public function searchByDni(string $dni): Collection;
}
