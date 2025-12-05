<?php

namespace Modules\JobProfile\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\BaseService;
use Modules\JobProfile\Entities\JobProfile;
use Modules\JobProfile\Repositories\JobProfileRepository;
use Modules\Core\Exceptions\BusinessRuleException;
use Modules\JobProfile\Events\JobProfileCreated;

class JobProfileService extends BaseService
{
    public function __construct(JobProfileRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Obtiene todos los perfiles visibles para el usuario actual
     */
    public function getAll(): Collection
    {
        $user = auth()->user();

        return JobProfile::visibleFor($user)
            ->with(['positionCode', 'organizationalUnit', 'requestedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtiene perfiles por estado visibles para el usuario actual
     */
    public function getByStatus(string $status): Collection
    {
        $user = auth()->user();

        return JobProfile::byStatus($status)
            ->visibleFor($user)
            ->with(['positionCode', 'organizationalUnit', 'requestedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtiene un perfil por ID
     */
    public function findById(string $id): ?JobProfile
    {
        return JobProfile::with([
            'positionCode',
            'organizationalUnit',
            'requestingUnit',
            'requestedBy',
            'reviewedBy',
            'approvedBy',
            'requirements',
            'responsibilities',
            'vacancies',
            'history'
        ])->find($id);
    }

    /**
     * Crea un nuevo perfil de puesto
     */
    public function create(array $data, array $requirements = [], array $responsibilities = []): JobProfile
    {
        // Reintentar hasta 3 veces en caso de código duplicado
        $maxAttempts = 3;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            try {
                return DB::transaction(function () use ($data, $requirements, $responsibilities) {
                    // Generar código único si no se proporciona
                    if (!isset($data['code'])) {
                        $data['code'] = $this->generateCode($data['job_posting_id'] ?? null);
                    }

                    // Establecer estado inicial y usuario solicitante
                    $data['status'] = 'draft';
                    $data['requested_by'] = $data['requested_by'] ?? auth()->id();

                    $profile = JobProfile::create($data);

                    // El historial se registra automáticamente mediante el Observer

                    // Crear requisitos
                    foreach ($requirements as $index => $requirement) {
                        $profile->requirements()->create([
                            'category' => $requirement['category'],
                            'description' => $requirement['description'],
                            'is_mandatory' => $requirement['is_mandatory'] ?? true,
                            'order' => $index + 1,
                        ]);
                    }

                    // Crear responsabilidades
                    foreach ($responsibilities as $index => $responsibility) {
                        $profile->responsibilities()->create([
                            'description' => $responsibility['description'],
                            'order' => $index + 1,
                        ]);
                    }

                    // Disparar evento
                    event(new JobProfileCreated($profile));

                    return $profile->fresh(['requirements', 'responsibilities']);
                });
            } catch (\Illuminate\Database\QueryException $e) {
                // Si es error de clave duplicada, reintentar
                if ($e->getCode() === '23505' || strpos($e->getMessage(), 'unique constraint') !== false) {
                    $attempt++;
                    if ($attempt >= $maxAttempts) {
                        throw new BusinessRuleException(
                            'No se pudo generar un código único después de varios intentos. Por favor, intente nuevamente.'
                        );
                    }
                    // Quitar el código generado para forzar uno nuevo en el siguiente intento
                    unset($data['code']);
                    usleep(100000); // Esperar 100ms antes de reintentar
                    continue;
                }
                throw $e;
            }
        }
    }

    /**
     * Actualiza un perfil de puesto
     */
    public function update(string $id, array $data): JobProfile
    {
        return DB::transaction(function () use ($id, $data) {
            $profile = $this->repository->findOrFail($id);

            // Validar que se pueda editar
            if (!$profile->canEdit()) {
                throw new BusinessRuleException(
                    'No se puede modificar un perfil en estado: ' . $profile->status_label
                );
            }

            $profile->update($data);

            return $profile->fresh();
        });
    }

    /**
     * Actualiza requisitos del perfil
     */
    public function updateRequirements(string $id, array $requirements): JobProfile
    {
        return DB::transaction(function () use ($id, $requirements) {
            $profile = $this->repository->findOrFail($id);

            if (!$profile->canEdit()) {
                throw new BusinessRuleException('No se pueden modificar los requisitos de este perfil.');
            }

            // Eliminar requisitos existentes
            $profile->requirements()->delete();

            // Crear nuevos requisitos
            foreach ($requirements as $index => $requirement) {
                $profile->requirements()->create([
                    'category' => $requirement['category'],
                    'description' => $requirement['description'],
                    'is_mandatory' => $requirement['is_mandatory'] ?? true,
                    'order' => $index + 1,
                ]);
            }

            return $profile->fresh(['requirements']);
        });
    }

    /**
     * Actualiza responsabilidades del perfil
     */
    public function updateResponsibilities(string $id, array $responsibilities): JobProfile
    {
        return DB::transaction(function () use ($id, $responsibilities) {
            $profile = $this->repository->findOrFail($id);

            if (!$profile->canEdit()) {
                throw new BusinessRuleException('No se pueden modificar las responsabilidades de este perfil.');
            }

            // Eliminar responsabilidades existentes
            $profile->responsibilities()->delete();

            // Crear nuevas responsabilidades
            foreach ($responsibilities as $index => $responsibility) {
                $profile->responsibilities()->create([
                    'description' => $responsibility['description'],
                    'order' => $index + 1,
                ]);
            }

            return $profile->fresh(['responsibilities']);
        });
    }

    /**
     * Elimina un perfil de puesto
     */
    public function delete(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $profile = $this->repository->findOrFail($id);

            // Solo se pueden eliminar perfiles en borrador o rechazados
            if (!in_array($profile->status, ['draft', 'rejected'])) {
                throw new BusinessRuleException(
                    'Solo se pueden eliminar perfiles en borrador o rechazados.'
                );
            }

            return $profile->delete();
        });
    }

    /**
     * Genera un código único para el perfil
     * Formato: PROF-2025-001 o CONV-2025-001-01 si está asociado a convocatoria
     */
    protected function generateCode(?string $jobPostingId = null): string
    {
        $year = now()->year;

        if ($jobPostingId) {
            // Obtener el código de la convocatoria si existe el módulo
            if (class_exists('\Modules\JobPosting\Entities\JobPosting')) {
                $jobPosting = \Modules\JobPosting\Entities\JobPosting::find($jobPostingId);
                if ($jobPosting) {
                    // Contar perfiles de esta convocatoria con bloqueo pesimista
                    $count = JobProfile::where('job_posting_id', $jobPostingId)
                        ->lockForUpdate()
                        ->count() + 1;
                    return $jobPosting->code . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
                }
            }
        }

        // Código independiente con bloqueo pesimista
        $count = JobProfile::whereNull('job_posting_id')
            ->whereYear('created_at', $year)
            ->lockForUpdate()
            ->count() + 1;

        return 'PROF-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
