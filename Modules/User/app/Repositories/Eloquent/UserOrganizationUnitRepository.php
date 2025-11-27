<?php

namespace Modules\User\Repositories\Eloquent;

use Modules\User\Repositories\Contracts\UserOrganizationUnitRepositoryInterface;
use Modules\User\Entities\UserOrganizationUnit;
use Illuminate\Support\Collection;

/**
 * Implementación del Repository para UserOrganizationUnit
 */
class UserOrganizationUnitRepository implements UserOrganizationUnitRepositoryInterface
{
    protected $model;

    public function __construct(UserOrganizationUnit $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data)
    {
        $assignment = $this->find($id);
        $assignment->update($data);
        return $assignment->fresh();
    }

    public function delete(string $id): bool
    {
        $assignment = $this->find($id);
        return $assignment->delete();
    }

    public function find(string $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Obtener asignaciones activas de un usuario
     */
    public function getUserActiveAssignments(string $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->active()
            ->current()
            ->with(['organizationUnit'])
            ->orderBy('is_primary', 'desc')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Obtener todas las asignaciones de un usuario (históricas)
     */
    public function getUserAssignments(string $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->with(['organizationUnit'])
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Obtener la asignación primaria activa de un usuario
     */
    public function getPrimaryAssignment(string $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->primary()
            ->active()
            ->current()
            ->with(['organizationUnit'])
            ->first();
    }

    /**
     * Obtener todas las asignaciones primarias de un usuario (para desactivar)
     */
    public function getUserPrimaryAssignments(string $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Obtener usuarios asignados a una unidad organizacional
     */
    public function getUnitUsers(string $unitId, bool $onlyActive = true): Collection
    {
        $query = $this->model
            ->where('organization_unit_id', $unitId)
            ->with(['user']);

        if ($onlyActive) {
            $query->active()->current();
        }

        return $query->orderBy('is_primary', 'desc')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Verificar si un usuario está asignado a una unidad (activo)
     */
    public function isUserAssignedToUnit(string $userId, string $unitId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('organization_unit_id', $unitId)
            ->active()
            ->current()
            ->exists();
    }

    /**
     * Obtener asignaciones expiradas que aún están activas
     */
    public function getExpiredAssignments(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->get();
    }

    /**
     * Contar asignaciones activas por unidad
     */
    public function countActiveUsersByUnit(string $unitId): int
    {
        return $this->model
            ->where('organization_unit_id', $unitId)
            ->active()
            ->current()
            ->count();
    }

    /**
     * Obtener asignaciones que expiran pronto
     */
    public function getExpiringSoon(int $days = 30): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays($days)])
            ->with(['user', 'organizationUnit'])
            ->get();
    }

    /**
     * Buscar asignaciones con filtros avanzados
     */
    public function search(array $filters)
    {
        $query = $this->model->with(['user', 'organizationUnit']);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['organization_unit_id'])) {
            $query->where('organization_unit_id', $filters['organization_unit_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_primary'])) {
            $query->where('is_primary', $filters['is_primary']);
        }

        if (isset($filters['start_date_from'])) {
            $query->where('start_date', '>=', $filters['start_date_from']);
        }

        if (isset($filters['start_date_to'])) {
            $query->where('start_date', '<=', $filters['start_date_to']);
        }

        if (isset($filters['current_only']) && $filters['current_only']) {
            $query->current();
        }

        return $query;
    }
}