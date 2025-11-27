<?php

namespace Modules\JobPosting\Services;

use Illuminate\Support\Facades\DB;
use Modules\JobPosting\Entities\{JobPosting, JobPostingHistory, ProcessPhase};
use Modules\User\Entities\User;
use Illuminate\Pagination\LengthAwarePaginator;

class JobPostingService
{
    /**
     * Listar convocatorias con filtros
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = JobPosting::query()
            ->with(['publisher', 'schedules.phase']);

        // Filtro por año
        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        // Filtro por estado
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Búsqueda por texto
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por rango de fechas
        if (!empty($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('end_date', '<=', $filters['end_date']);
        }

        // Ordenamiento
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Crear nueva convocatoria
     */
    public function create(array $data, ?User $user = null): JobPosting
    {
        return DB::transaction(function() use ($data, $user) {
            $jobPosting = JobPosting::create($data);

            // Registrar en historial
            JobPostingHistory::log(
                $jobPosting,
                'created',
                $user,
                null,
                $jobPosting->status->value,
                description: 'Convocatoria creada'
            );

            return $jobPosting->load('schedules', 'publisher');
        });
    }

    /**
     * Actualizar convocatoria
     */
    public function update(JobPosting $jobPosting, array $data, ?User $user = null): JobPosting
    {
        return DB::transaction(function() use ($jobPosting, $data, $user) {
            $oldValues = $jobPosting->only(array_keys($data));
            $oldStatus = $jobPosting->status->value;

            $jobPosting->update($data);

            // Registrar cambio en historial
            JobPostingHistory::log(
                $jobPosting,
                'updated',
                $user,
                $oldStatus,
                $jobPosting->status->value,
                $oldValues,
                $data,
                description: 'Convocatoria actualizada'
            );

            return $jobPosting->fresh();
        });
    }

    /**
     * Publicar convocatoria
     */
    public function publish(JobPosting $jobPosting, User $user): JobPosting
    {
        if (!$jobPosting->canBePublished()) {
            throw new \Exception('La convocatoria no puede ser publicada. Verifique que tenga un cronograma completo.');
        }

        return DB::transaction(function() use ($jobPosting, $user) {
            $oldStatus = $jobPosting->status->value;
            
            $jobPosting->publish($user);

            // Registrar en historial
            JobPostingHistory::log(
                $jobPosting,
                'published',
                $user,
                $oldStatus,
                $jobPosting->status->value,
                description: 'Convocatoria publicada oficialmente'
            );

            return $jobPosting->fresh();
        });
    }

    /**
     * Iniciar proceso de convocatoria
     */
    public function startProcess(JobPosting $jobPosting, User $user): JobPosting
    {
        if (!$jobPosting->isPublished()) {
            throw new \Exception('La convocatoria debe estar publicada para iniciar el proceso.');
        }

        return DB::transaction(function() use ($jobPosting, $user) {
            $oldStatus = $jobPosting->status->value;
            
            $jobPosting->startProcess();

            // Registrar en historial
            JobPostingHistory::log(
                $jobPosting,
                'started',
                $user,
                $oldStatus,
                $jobPosting->status->value,
                description: 'Proceso de convocatoria iniciado'
            );

            return $jobPosting->fresh();
        });
    }

    /**
     * Finalizar convocatoria
     */
    public function finalize(JobPosting $jobPosting, User $user): JobPosting
    {
        if (!$jobPosting->isInProcess()) {
            throw new \Exception('La convocatoria debe estar en proceso para finalizarse.');
        }

        return DB::transaction(function() use ($jobPosting, $user) {
            $oldStatus = $jobPosting->status->value;
            
            $jobPosting->finalize($user);

            // Registrar en historial
            JobPostingHistory::log(
                $jobPosting,
                'finalized',
                $user,
                $oldStatus,
                $jobPosting->status->value,
                description: 'Convocatoria finalizada exitosamente'
            );

            return $jobPosting->fresh();
        });
    }

    /**
     * Cancelar convocatoria
     */
    public function cancel(JobPosting $jobPosting, string $reason, User $user): JobPosting
    {
        if (!$jobPosting->canBeCancelled()) {
            throw new \Exception('Esta convocatoria no puede ser cancelada.');
        }

        return DB::transaction(function() use ($jobPosting, $reason, $user) {
            $oldStatus = $jobPosting->status->value;
            
            $jobPosting->cancel($user, $reason);

            // Registrar en historial
            JobPostingHistory::log(
                $jobPosting,
                'cancelled',
                $user,
                $oldStatus,
                $jobPosting->status->value,
                reason: $reason,
                description: 'Convocatoria cancelada'
            );

            return $jobPosting->fresh();
        });
    }

    /**
     * Eliminar convocatoria (soft delete)
     */
    public function delete(JobPosting $jobPosting, ?User $user = null): bool
    {
        return DB::transaction(function() use ($jobPosting, $user) {
            // Registrar en historial antes de eliminar
            JobPostingHistory::log(
                $jobPosting,
                'deleted',
                $user,
                $jobPosting->status->value,
                null,
                description: 'Convocatoria eliminada (soft delete)'
            );

            return $jobPosting->delete();
        });
    }

    /**
     * Clonar convocatoria
     */
    public function clone(JobPosting $original, ?User $user = null): JobPosting
    {
        return DB::transaction(function() use ($original, $user) {
            // Crear nueva convocatoria con datos del original
            $data = $original->only([
                'title',
                'description',
                'start_date',
                'end_date',
            ]);
            
            // Agregar año actual
            $data['year'] = now()->year;

            $newJobPosting = JobPosting::create($data);

            // Clonar cronograma
            foreach ($original->schedules as $schedule) {
                $newJobPosting->schedules()->create([
                    'process_phase_id' => $schedule->process_phase_id,
                    'start_date' => $schedule->start_date,
                    'end_date' => $schedule->end_date,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'location' => $schedule->location,
                    'responsible_unit_id' => $schedule->responsible_unit_id,
                    'notes' => $schedule->notes,
                ]);
            }

            // Registrar en historial
            JobPostingHistory::log(
                $newJobPosting,
                'cloned',
                $user,
                null,
                $newJobPosting->status->value,
                description: "Convocatoria clonada desde {$original->code}"
            );

            return $newJobPosting->load('schedules.phase');
        });
    }

    /**
     * Obtener estadísticas de convocatorias
     */
    public function getStatistics(?int $year = null): array
    {
        $query = JobPosting::query();

        if ($year) {
            $query->where('year', $year);
        }

        return [
            'total' => $query->count(),
            'por_estado' => [
                'borradores' => (clone $query)->draft()->count(),
                'publicadas' => (clone $query)->published()->count(),
                'en_proceso' => (clone $query)->where('status', 'EN_PROCESO')->count(),
                'finalizadas' => (clone $query)->where('status', 'FINALIZADA')->count(),
                'canceladas' => (clone $query)->where('status', 'CANCELADA')->count(),
            ],
            'activas' => (clone $query)->active()->count(),
            'por_mes' => $this->getMonthlyDistribution($year),
        ];
    }

    /**
     * Distribución por mes
     */
    protected function getMonthlyDistribution(?int $year): array
    {
        $query = JobPosting::query()
            ->selectRaw('MONTH(start_date) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month');

        if ($year) {
            $query->where('year', $year);
        }

        $results = $query->get();

        $distribution = array_fill(1, 12, 0);
        foreach ($results as $result) {
            $distribution[$result->month] = $result->count;
        }

        return $distribution;
    }

    /**
     * Obtener años disponibles
     */
    public function getAvailableYears()
    {
        return JobPosting::query()
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }

    /**
     * Obtener convocatorias próximas a vencer
     */
    public function getNearingEnd(int $days = 7)
    {
        return JobPosting::query()
            ->active()
            ->whereNotNull('end_date')
            ->whereRaw('DATEDIFF(end_date, CURDATE()) BETWEEN 0 AND ?', [$days])
            ->with(['schedules.phase', 'publisher'])
            ->get();
    }

    /**
     * Obtener convocatorias retrasadas
     */
    public function getDelayed()
    {
        return JobPosting::query()
            ->active()
            ->whereHas('schedules', function($q) {
                $q->where('end_date', '<', now())
                  ->where('status', '!=', 'COMPLETED');
            })
            ->with(['schedules' => function($q) {
                $q->where('end_date', '<', now())
                  ->where('status', '!=', 'COMPLETED');
            }])
            ->get();
    }
}