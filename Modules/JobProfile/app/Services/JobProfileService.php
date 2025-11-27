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
     * Obtiene todos los perfiles
     */
    public function getAll(): Collection
    {
        return JobProfile::with(['positionCode', 'organizationalUnit', 'requestedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtiene perfiles por estado
     */
    public function getByStatus(string $status): Collection
    {
        return JobProfile::byStatus($status)
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
        return DB::transaction(function () use ($data, $requirements, $responsibilities) {
            // Generar código único si no se proporciona
            if (!isset($data['code'])) {
                $data['code'] = $this->generateCode($data['job_posting_id'] ?? null);
            }

            // Establecer estado inicial y usuario solicitante
            $data['status'] = 'draft';
            $data['requested_by'] = $data['requested_by'] ?? auth()->id();

            $profile = JobProfile::create($data);

            // Registrar en historial
            \Modules\JobProfile\Entities\JobProfileHistory::log(
                $profile->id,
                'created',
                auth()->id(),
                null,
                'draft',
                'Perfil de puesto creado'
            );

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
                    // Contar perfiles de esta convocatoria
                    $count = JobProfile::where('job_posting_id', $jobPostingId)->count() + 1;
                    return $jobPosting->code . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
                }
            }
        }

        // Código independiente
        $count = JobProfile::whereNull('job_posting_id')
            ->whereYear('created_at', $year)
            ->count() + 1;

        return 'PROF-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
