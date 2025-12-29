<?php

namespace Modules\JobPosting\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Modules\JobPosting\Entities\{JobPosting, JobPostingHistory, ProcessPhase, JobPostingSchedule};
use Modules\User\Entities\User;
use Modules\JobPosting\Enums\JobPostingStatusEnum;
use Modules\JobPosting\Events\JobPostingPublicationRequested;
use Illuminate\Support\Facades\Log;


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
     * MEJORADO: Incluye generación de código único y prevención de duplicados
     */
    public function create(array $data, ?User $user = null): JobPosting
    {
        return DB::transaction(function() use ($data, $user) {
            // 1. Generar código único (soluciona el error de duplicidad)
            $data['code'] = $this->generateNextCode($data['year']);

            // 2. Establecer estado inicial
            $data['status'] = 'BORRADOR';

            // 3. Crear la convocatoria
            $jobPosting = JobPosting::create($data);

            // 4. Generar cronograma automático si se solicitó
            if (!empty($data['auto_schedule']) && $data['auto_schedule'] == true) {
                $startDate = $data['schedule_start_date'] ?? now();

                // MEJORA: Verificar si ya existe cronograma para evitar duplicados
                if ($jobPosting->schedules()->count() === 0) {
                    $this->generateAutomaticSchedule($jobPosting, $startDate);
                }
            }

            // 5. Registrar en historial
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
     * Publicar convocatoria (inicia flujo de firma)
     */
    public function publish(JobPosting $jobPosting, User $user): JobPosting
    {
        // Validar estado actual
        if (!$jobPosting->status->canBePublished()) {
            throw new \Exception('La convocatoria no puede ser publicada en su estado actual');
        }

        // Validar que tenga al menos un perfil aprobado
        $approvedProfilesCount = $jobPosting->jobProfiles()
            ->where('status', 'approved')
            ->count();

        if ($approvedProfilesCount === 0) {
            throw new \Exception('La convocatoria debe tener al menos un perfil aprobado para ser publicada');
        }

        return DB::transaction(function() use ($jobPosting, $user, $approvedProfilesCount) {
            $oldStatus = $jobPosting->status->value;

            // CAMBIO: Ya no publicar directamente, pasar a EN_FIRMA
            $jobPosting->status = JobPostingStatusEnum::EN_FIRMA;
            $jobPosting->save();

            // Registrar en historial
            JobPostingHistory::log(
                $jobPosting,
                'sent_to_signature',
                $user,
                $oldStatus,
                $jobPosting->status->value,
                description: 'Convocatoria enviada a proceso de firma'
            );

            // Disparar evento para iniciar flujo de generación de documento y firma
            event(new JobPostingPublicationRequested($jobPosting));

            Log::info('Convocatoria enviada a proceso de firma', [
                'job_posting_id' => $jobPosting->id,
                'approved_profiles' => $approvedProfilesCount,
            ]);

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

            // Asignar al año actual
            $data['year'] = now()->year;
            // Generar NUEVO código para el año actual
            $data['code'] = $this->generateNextCode($data['year']);
            $data['status'] = 'BORRADOR';

            $newJobPosting = JobPosting::create($data);

            // Clonar cronograma SIN duplicados
            $this->cloneSchedule($original, $newJobPosting);

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

    /* -------------------------------------------------------------------------- */
    /*                              MÉTODOS PRIVADOS MEJORADOS                    */
    /* -------------------------------------------------------------------------- */

    /**
     * Genera el siguiente código disponible: CONV-2025-001
     * MEJORADO: Considera registros eliminados para evitar colisiones
     */
    private function generateNextCode(int $year): string
    {
        // Buscamos la última convocatoria de ese año, INCLUYENDO las eliminadas
        $lastPosting = JobPosting::withTrashed()
            ->where('year', $year)
            ->where('code', 'LIKE', "CONV-{$year}-%")
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastPosting) {
            return "CONV-{$year}-001";
        }

        // Extraer número. Ej: CONV-2025-005 -> 5
        $parts = explode('-', $lastPosting->code);
        $lastNumber = (int) end($parts);

        // Incrementar
        $nextNumber = $lastNumber + 1;

        // Formatear con ceros a la izquierda
        return "CONV-{$year}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Genera las 12 fases automáticamente SIN DUPLICADOS
     * MEJORADO: Usa updateOrCreate y validaciones
     */
    private function generateAutomaticSchedule(JobPosting $jobPosting, $startDate): void
    {
        // MEJORA: Verificar que no exista cronograma previo
        $existingCount = $jobPosting->schedules()->count();
        if ($existingCount > 0) {
            \Log::warning("Intento de generar cronograma duplicado para JobPosting ID: {$jobPosting->id}");
            return; // No generar si ya existe
        }

        // Obtener fases activas ordenadas por phase_number
        $phases = ProcessPhase::where('is_active', true)
                    ->orderBy('phase_number', 'asc')
                    ->get();

        if ($phases->isEmpty()) {
            throw new \Exception('No hay fases activas configuradas en el sistema.');
        }

        $currentDate = $startDate instanceof \Carbon\Carbon ? $startDate : \Carbon\Carbon::parse($startDate);

        foreach ($phases as $phase) {
            // Lógica de duración por fase (mejorada)
            $daysDuration = $phase->default_duration_days ?? match($phase->phase_number) {
                3 => 2, // Registro de postulantes
                6 => 3, // Evaluación curricular
                8 => 2, // Entrevista
                default => 1
            };

            // Calcular fecha fin
            $endDate = (clone $currentDate)->addDays($daysDuration - 1);

            // CRÍTICO: Usar updateOrCreate para evitar duplicados
            $jobPosting->schedules()->updateOrCreate(
                [
                    'process_phase_id' => $phase->id // Clave única
                ],
                [
                    'start_date' => $currentDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'status' => 'PENDING',
                    'notify_before' => true,
                    'location' => 'Portal Institucional',
                    'notify_days_before' => 3,
                ]
            );

            // La siguiente fase empieza al día siguiente
            $currentDate = (clone $endDate)->addDay();
        }

        \Log::info("Cronograma generado exitosamente para JobPosting ID: {$jobPosting->id}. Total fases: {$phases->count()}");
    }

    /**
     * Clonar cronograma SIN duplicados
     * NUEVO MÉTODO
     */
    private function cloneSchedule(JobPosting $original, JobPosting $newJobPosting): void
    {
        foreach ($original->schedules as $schedule) {
            // Usar updateOrCreate para evitar duplicados
            $newJobPosting->schedules()->updateOrCreate(
                [
                    'process_phase_id' => $schedule->process_phase_id
                ],
                [
                    'start_date' => $schedule->start_date,
                    'end_date' => $schedule->end_date,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'location' => $schedule->location,
                    'responsible_unit_id' => $schedule->responsible_unit_id,
                    'notes' => $schedule->notes,
                    'status' => 'PENDING',
                    'notify_before' => true,
                    'notify_days_before' => 3,
                ]
            );
        }
    }

    /**
     * Regenerar cronograma (elimina el anterior)
     * NUEVO MÉTODO
     */
    public function regenerateSchedule(JobPosting $jobPosting, $startDate): JobPosting
    {
        return DB::transaction(function() use ($jobPosting, $startDate) {
            // Eliminar cronograma existente
            $jobPosting->schedules()->forceDelete();

            // Generar nuevo cronograma
            $this->generateAutomaticSchedule($jobPosting, $startDate);

            return $jobPosting->fresh('schedules.phase');
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                              MÉTODOS DE ESTADÍSTICA                        */
    /* -------------------------------------------------------------------------- */

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

    public function getAvailableYears()
    {
        return JobPosting::query()
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }

    public function getNearingEnd(int $days = 7)
    {
        return JobPosting::query()
            ->active()
            ->whereNotNull('end_date')
            ->whereRaw('DATEDIFF(end_date, CURDATE()) BETWEEN 0 AND ?', [$days])
            ->with(['schedules.phase', 'publisher'])
            ->get();
    }

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
    //--------METODOS PARA OTROS MOCULOS---------

    public function getActivePostings($limit = 10) 
    {
        return JobPosting::where('status', JobPostingStatusEnum::EN_PROCESO)
            ->with(['organizationalUnit', 'profiles'])
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
