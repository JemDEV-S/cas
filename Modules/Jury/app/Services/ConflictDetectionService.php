<?php

namespace Modules\Jury\Services;

use Modules\Jury\Entities\JuryConflict;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Servicio para gestionar conflictos de interés (simplificado)
 *
 * Según diseño optimizado:
 * - Solo conflictos manuales: FAMILY y PERSONAL
 * - Sin workflow complejo (sin severity, status, resolution)
 * - Usa user_id directamente
 * - Campos mínimos: user_id, application_id, type, description
 */
class ConflictDetectionService
{
    /**
     * Reportar un conflicto de interés manual
     *
     * @param array $data ['user_id', 'application_id', 'type', 'description']
     * @return JuryConflict
     */
    public function report(array $data): JuryConflict
    {
        // Validar que el tipo sea FAMILY o PERSONAL
        if (!in_array($data['type'], ['FAMILY', 'PERSONAL'])) {
            throw new \Exception('El tipo de conflicto debe ser FAMILY o PERSONAL');
        }

        // Verificar que no exista ya un conflicto para esta combinación
        $existing = JuryConflict::where('user_id', $data['user_id'])
            ->where('application_id', $data['application_id'])
            ->first();

        if ($existing) {
            throw new \Exception('Ya existe un conflicto reportado para este jurado y postulación');
        }

        $conflict = JuryConflict::create([
            'user_id' => $data['user_id'],
            'application_id' => $data['application_id'],
            'type' => $data['type'],
            'description' => $data['description'],
        ]);

        return $conflict->fresh(['user', 'application']);
    }

    /**
     * Verificar si existe conflicto entre un jurado y una postulación
     *
     * @param int $userId
     * @param string $applicationId
     * @return bool
     */
    public function hasConflict(int $userId, string $applicationId): bool
    {
        return JuryConflict::hasConflict($userId, $applicationId);
    }

    /**
     * Obtener conflictos de un usuario
     *
     * @param int $userId
     * @return Collection
     */
    public function getConflictsByUser(int $userId): Collection
    {
        return JuryConflict::byUser($userId)
            ->with(['application'])
            ->get();
    }

    /**
     * Obtener conflictos para una postulación
     *
     * @param string $applicationId
     * @return Collection
     */
    public function getConflictsByApplication(string $applicationId): Collection
    {
        return JuryConflict::byApplication($applicationId)
            ->with(['user'])
            ->get();
    }

    /**
     * Get conflicted users (jurors) for an application
     *
     * @param string $applicationId
     * @return Collection
     */
    public function getConflictedUsers(string $applicationId): Collection
    {
        return User::whereHas('juryConflicts', function ($query) use ($applicationId) {
            $query->where('application_id', $applicationId);
        })->get();
    }

    /**
     * Eliminar un conflicto
     *
     * @param string $conflictId
     * @return bool
     */
    public function deleteConflict(string $conflictId): bool
    {
        $conflict = JuryConflict::findOrFail($conflictId);
        return $conflict->delete();
    }

    /**
     * Actualizar descripción de conflicto
     *
     * @param string $conflictId
     * @param string $description
     * @return JuryConflict
     */
    public function updateDescription(string $conflictId, string $description): JuryConflict
    {
        $conflict = JuryConflict::findOrFail($conflictId);
        $conflict->update(['description' => $description]);

        return $conflict->fresh();
    }

    /**
     * Obtener estadísticas de conflictos
     *
     * @param array $filters ['job_posting_id'?, 'user_id'?]
     * @return array
     */
    public function getStatistics(array $filters = []): array
    {
        $query = JuryConflict::query();

        if (!empty($filters['job_posting_id'])) {
            $query->whereHas('application', function($q) use ($filters) {
                $q->where('job_profile_vacancy_id', $filters['job_posting_id']);
            });
        }

        if (!empty($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        $conflicts = $query->with(['user', 'application'])->get();

        return [
            'total' => $conflicts->count(),
            'by_type' => [
                'family' => $conflicts->where('type', 'FAMILY')->count(),
                'personal' => $conflicts->where('type', 'PERSONAL')->count(),
            ],
            'unique_users' => $conflicts->pluck('user_id')->unique()->count(),
            'unique_applications' => $conflicts->pluck('application_id')->unique()->count(),
        ];
    }

    /**
     * Verificar conflictos automáticos basados en dependencia
     * (Conflicto automático: jurado.dependencia == perfil.dependencia)
     *
     * @param int $userId
     * @param string $applicationId
     * @return bool
     */
    public function checkAutomaticDependencyConflict(int $userId, string $applicationId): bool
    {
        $application = \Modules\Application\Entities\Application::with('profile')->findOrFail($applicationId);
        $user = User::with('dependency')->findOrFail($userId);

        // Si el jurado tiene una dependencia y coincide con la del perfil, hay conflicto automático
        if ($user->dependency_id && $application->profile && $application->profile->dependency_id) {
            return $user->dependency_id == $application->profile->dependency_id;
        }

        return false;
    }

    /**
     * Buscar conflictos potenciales (sugerencias) para revisar manualmente
     * Nota: Los conflictos automáticos por dependencia se manejan en JuryAssignmentService
     *
     * @param int $userId
     * @param string $applicationId
     * @return array
     */
    public function suggestPotentialConflicts(int $userId, string $applicationId): array
    {
        $suggestions = [];

        // Verificar conflicto automático por dependencia
        if ($this->checkAutomaticDependencyConflict($userId, $applicationId)) {
            $suggestions[] = [
                'type' => 'DEPENDENCY',
                'severity' => 'AUTOMATIC',
                'description' => 'El jurado pertenece a la misma dependencia que el perfil del puesto',
                'prevents_assignment' => true,
            ];
        }

        // Verificar si ya tiene un conflicto manual reportado
        if ($this->hasConflict($userId, $applicationId)) {
            $conflict = JuryConflict::where('user_id', $userId)
                ->where('application_id', $applicationId)
                ->first();

            $suggestions[] = [
                'type' => $conflict->type,
                'severity' => 'MANUAL',
                'description' => $conflict->description,
                'prevents_assignment' => true,
            ];
        }

        return $suggestions;
    }
}
