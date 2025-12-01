<?php

namespace Modules\User\Services;

use Modules\User\Entities\User;
use Modules\User\Entities\UserOrganizationUnit;
use Modules\Organization\Entities\OrganizationalUnit;
use Modules\User\Repositories\Contracts\UserOrganizationUnitRepositoryInterface;
use Modules\User\Events\UserOrganizationAssigned;
use Modules\User\Events\UserOrganizationUnassigned;
use Modules\User\Events\UserOrganizationChanged;
use Modules\User\Exceptions\AssignmentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AssignmentService
{
    public function __construct(
        protected UserOrganizationUnitRepositoryInterface $repository
    ) {}

    /**
     * Asignar usuario a unidad organizacional
     */
    public function assignUserToUnit(
        User $user,
        OrganizationalUnit $organizationalUnit,
        Carbon $startDate,
        ?Carbon $endDate = null,
        bool $isPrimary = false
    ): UserOrganizationUnit {
        DB::beginTransaction();
        
        try {
            // Si se marca como primaria, desactivar otras primarias
            if ($isPrimary) {
                $this->deactivatePrimaryAssignments($user);
            }

            // Crear la asignación
            $assignment = $this->repository->create([
                'user_id' => $user->id,
                'organization_unit_id' => $organizationalUnit->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_primary' => $isPrimary,
                'is_active' => true,
            ]);

            // Disparar evento
            event(new UserOrganizationAssigned($user, $organizationalUnit, $assignment));

            DB::commit();

            Log::info('Usuario asignado a unidad organizacional', [
                'user_id' => $user->id,
                'organization_unit_id' => $organizationalUnit->id,
                'assignment_id' => $assignment->id,
            ]);

            return $assignment->load(['user', 'organizationUnit']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al asignar usuario a unidad organizacional', [
                'user_id' => $user->id,
                'organization_unit_id' => $organizationalUnit->id,
                'error' => $e->getMessage(),
            ]);
            throw new AssignmentException('Error al asignar usuario a unidad organizacional: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar asignación existente
     */
    public function updateAssignment(
        UserOrganizationUnit $assignment,
        array $data
    ): UserOrganizationUnit {
        DB::beginTransaction();
        
        try {
            // Si se cambia a primaria, desactivar otras primarias
            if (isset($data['is_primary']) && $data['is_primary']) {
                $this->deactivatePrimaryAssignments($assignment->user, $assignment->id);
            }

            $assignment = $this->repository->update($assignment->id, $data);

            DB::commit();

            Log::info('Asignación actualizada', [
                'assignment_id' => $assignment->id,
                'user_id' => $assignment->user_id,
            ]);

            return $assignment->load(['user', 'organizationUnit']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar asignación', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage(),
            ]);
            throw new AssignmentException('Error al actualizar asignación: ' . $e->getMessage());
        }
    }

    /**
     * Desasignar usuario de unidad organizacional
     */
    public function unassignUserFromUnit(
        UserOrganizationUnit $assignment,
        ?string $reason = null
    ): bool {
        DB::beginTransaction();
        
        try {
            $user = $assignment->user;
            $organizationalUnit = $assignment->organizationUnit;

            // Marcar como inactiva y establecer fecha de fin
            $this->repository->update($assignment->id, [
                'is_active' => false,
                'end_date' => now(),
            ]);

            // Disparar evento
            event(new UserOrganizationUnassigned($user, $organizationalUnit, $assignment, $reason));

            DB::commit();

            Log::info('Usuario desasignado de unidad organizacional', [
                'user_id' => $user->id,
                'organization_unit_id' => $organizationalUnit->id,
                'assignment_id' => $assignment->id,
                'reason' => $reason,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al desasignar usuario', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage(),
            ]);
            throw new AssignmentException('Error al desasignar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar unidad organizacional principal del usuario
     */
    public function changePrimaryUnit(
        User $user,
        OrganizationalUnit $newUnit,
        Carbon $startDate
    ): UserOrganizationUnit {
        DB::beginTransaction();
        
        try {
            // Obtener asignación primaria actual
            $currentPrimary = $this->repository->getPrimaryAssignment($user->id);

            // Desactivar la primaria actual
            if ($currentPrimary) {
                $this->repository->update($currentPrimary->id, [
                    'is_primary' => false,
                    'end_date' => $startDate->copy()->subDay(),
                ]);
            }

            // Crear o activar nueva asignación primaria
            $newAssignment = $this->assignUserToUnit(
                $user,
                $newUnit,
                $startDate,
                null,
                true
            );

            // Disparar evento
            event(new UserOrganizationChanged($user, $currentPrimary?->organizationUnit, $newUnit));

            DB::commit();

            Log::info('Unidad organizacional primaria cambiada', [
                'user_id' => $user->id,
                'old_unit_id' => $currentPrimary?->organization_unit_id,
                'new_unit_id' => $newUnit->id,
            ]);

            return $newAssignment;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar unidad primaria', [
                'user_id' => $user->id,
                'new_unit_id' => $newUnit->id,
                'error' => $e->getMessage(),
            ]);
            throw new AssignmentException('Error al cambiar unidad primaria: ' . $e->getMessage());
        }
    }

    /**
     * Obtener asignaciones activas de un usuario
     */
    public function getUserActiveAssignments(User $user): Collection
    {
        return $this->repository->getUserActiveAssignments($user->id);
    }

    /**
     * Obtener asignación primaria de un usuario
     */
    public function getUserPrimaryAssignment(User $user): ?UserOrganizationUnit
    {
        return $this->repository->getPrimaryAssignment($user->id);
    }

    /**
     * Obtener todas las asignaciones de un usuario (activas e históricas)
     */
    public function getUserAssignmentHistory(User $user): Collection
    {
        return $this->repository->getUserAssignments($user->id);
    }

    /**
     * Obtener usuarios asignados a una unidad organizacional
     */
    public function getUnitAssignedUsers(
        OrganizationalUnit $unit,
        bool $onlyActive = true
    ): Collection {
        return $this->repository->getUnitUsers($unit->id, $onlyActive);
    }

    /**
     * Asignar múltiples usuarios a una unidad
     */
    public function bulkAssignUsersToUnit(
        array $userIds,
        OrganizationalUnit $unit,
        Carbon $startDate,
        ?Carbon $endDate = null
    ): array {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($userIds as $userId) {
            try {
                $user = User::findOrFail($userId);
                $assignment = $this->assignUserToUnit($user, $unit, $startDate, $endDate);
                $results['success'][] = [
                    'user_id' => $userId,
                    'assignment_id' => $assignment->id,
                ];
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        Log::info('Asignación masiva completada', [
            'organization_unit_id' => $unit->id,
            'success_count' => count($results['success']),
            'failed_count' => count($results['failed']),
        ]);

        return $results;
    }

    /**
     * Transferir usuarios de una unidad a otra
     */
    public function transferUsers(
        OrganizationalUnit $fromUnit,
        OrganizationalUnit $toUnit,
        Carbon $transferDate
    ): array {
        DB::beginTransaction();
        
        try {
            $activeAssignments = $this->repository->getUnitUsers($fromUnit->id, true);
            $results = [
                'transferred' => 0,
                'failed' => 0,
            ];

            foreach ($activeAssignments as $assignment) {
                try {
                    // Terminar asignación actual
                    $this->repository->update($assignment->id, [
                        'is_active' => false,
                        'end_date' => $transferDate->copy()->subDay(),
                    ]);

                    // Crear nueva asignación
                    $this->assignUserToUnit(
                        $assignment->user,
                        $toUnit,
                        $transferDate,
                        null,
                        $assignment->is_primary
                    );

                    $results['transferred']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    Log::error('Error al transferir usuario', [
                        'user_id' => $assignment->user_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            Log::info('Transferencia de usuarios completada', [
                'from_unit_id' => $fromUnit->id,
                'to_unit_id' => $toUnit->id,
                'transferred' => $results['transferred'],
                'failed' => $results['failed'],
            ]);

            return $results;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new AssignmentException('Error en transferencia de usuarios: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si un usuario está asignado a una unidad específica
     */
    public function isUserAssignedToUnit(User $user, OrganizationalUnit $unit): bool
    {
        return $this->repository->isUserAssignedToUnit($user->id, $unit->id);
    }

    /**
     * Verificar si un usuario tiene asignaciones activas
     */
    public function hasActiveAssignments(User $user): bool
    {
        return $this->repository->getUserActiveAssignments($user->id)->isNotEmpty();
    }

    /**
     * Obtener estadísticas de asignaciones
     */
    public function getAssignmentStatistics(OrganizationalUnit $unit): array
    {
        $activeUsers = $this->repository->getUnitUsers($unit->id, true);
        $totalUsers = $this->repository->getUnitUsers($unit->id, false);

        return [
            'active_users' => $activeUsers->count(),
            'total_users' => $totalUsers->count(),
            'primary_assignments' => $activeUsers->where('is_primary', true)->count(),
            'secondary_assignments' => $activeUsers->where('is_primary', false)->count(),
        ];
    }

    /**
     * Finalizar asignaciones que han expirado
     */
    public function finalizeExpiredAssignments(): int
    {
        $expired = $this->repository->getExpiredAssignments();
        $count = 0;

        foreach ($expired as $assignment) {
            try {
                $this->repository->update($assignment->id, [
                    'is_active' => false,
                ]);
                $count++;
            } catch (\Exception $e) {
                Log::error('Error al finalizar asignación expirada', [
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($count > 0) {
            Log::info('Asignaciones expiradas finalizadas', ['count' => $count]);
        }

        return $count;
    }

    /**
     * Desactivar todas las asignaciones primarias de un usuario excepto una específica
     */
    protected function deactivatePrimaryAssignments(User $user, ?string $exceptId = null): void
    {
        $primaryAssignments = $this->repository->getUserPrimaryAssignments($user->id);

        foreach ($primaryAssignments as $assignment) {
            if ($assignment->id !== $exceptId) {
                $this->repository->update($assignment->id, [
                    'is_primary' => false,
                ]);
            }
        }
    }
}