<?php

namespace Modules\JobProfile\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        Log::info('JobProfile Create - Iniciando creación de perfil', [
            'job_posting_id' => $data['job_posting_id'] ?? null,
            'title' => $data['title'] ?? null,
        ]);

        while ($attempt < $maxAttempts) {
            $attempt++;
            Log::info('JobProfile Create - Intento #' . $attempt);

            try {
                return DB::transaction(function () use ($data, $requirements, $responsibilities, $attempt) {
                    // Generar código único si no se proporciona
                    if (!isset($data['code'])) {
                        $data['code'] = $this->generateCode($data['job_posting_id'] ?? null);
                        Log::info('JobProfile Create - Código generado', [
                            'attempt' => $attempt,
                            'code' => $data['code'],
                        ]);
                    }

                    // Establecer estado inicial y usuario solicitante
                    $data['status'] = 'draft';
                    $data['requested_by'] = $data['requested_by'] ?? auth()->id();

                    Log::info('JobProfile Create - Intentando crear registro en BD', [
                        'code' => $data['code'],
                        'attempt' => $attempt,
                    ]);

                    $profile = JobProfile::create($data);

                    Log::info('JobProfile Create - Perfil creado exitosamente', [
                        'id' => $profile->id,
                        'code' => $profile->code,
                    ]);

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
                // MySQL: código 23000, PostgreSQL: código 23505
                $errorCode = $e->errorInfo[1] ?? null;
                $isDuplicateKey = $errorCode === 1062 || // MySQL duplicate entry
                                  $e->getCode() === '23000' || // MySQL integrity constraint
                                  $e->getCode() === '23505' || // PostgreSQL unique violation
                                  strpos($e->getMessage(), 'Duplicate entry') !== false ||
                                  strpos($e->getMessage(), 'unique constraint') !== false;

                Log::error('JobProfile Create - Error de base de datos', [
                    'attempt' => $attempt,
                    'code' => $data['code'] ?? 'N/A',
                    'error_code' => $errorCode,
                    'sqlstate' => $e->getCode(),
                    'is_duplicate' => $isDuplicateKey,
                    'message' => $e->getMessage(),
                ]);

                if ($isDuplicateKey) {
                    if ($attempt >= $maxAttempts) {
                        Log::error('JobProfile Create - Máximo de intentos alcanzado', [
                            'attempts' => $attempt,
                            'last_code' => $data['code'] ?? 'N/A',
                        ]);
                        throw new BusinessRuleException(
                            'No se pudo generar un código único después de varios intentos. Por favor, intente nuevamente.'
                        );
                    }
                    // Quitar el código generado para forzar uno nuevo en el siguiente intento
                    unset($data['code']);
                    Log::warning('JobProfile Create - Reintentando con nuevo código', [
                        'attempt' => $attempt,
                        'next_attempt' => $attempt + 1,
                    ]);
                    usleep(100000); // Esperar 100ms antes de reintentar
                    continue;
                }

                Log::error('JobProfile Create - Error no manejado, lanzando excepción');
                throw $e;
            }
        }

        Log::error('JobProfile Create - Salió del bucle sin retornar (esto no debería ocurrir)');
        throw new BusinessRuleException('Error inesperado al crear el perfil.');
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

        Log::info('JobProfile generateCode - Iniciando generación', [
            'job_posting_id' => $jobPostingId,
            'year' => $year,
        ]);

        if ($jobPostingId) {
            // Obtener el código de la convocatoria si existe el módulo
            if (class_exists('\Modules\JobPosting\Entities\JobPosting')) {
                $jobPosting = \Modules\JobPosting\Entities\JobPosting::find($jobPostingId);
                if ($jobPosting) {
                    Log::info('JobProfile generateCode - Convocatoria encontrada', [
                        'job_posting_code' => $jobPosting->code,
                    ]);

                    // Obtener el último perfil de esta convocatoria con bloqueo pesimista
                    // Usar DB::table() para evitar global scopes
                    $lastProfile = DB::table('job_profiles')
                        ->where('job_posting_id', $jobPostingId)
                        ->whereNull('deleted_at') // Considerar soft deletes manualmente
                        ->orderBy('code', 'desc')
                        ->lockForUpdate()
                        ->first();

                    $nextNumber = 1;
                    if ($lastProfile) {
                        // Extraer el número del código (ej: "CONV-2025-001-05" -> 5)
                        $parts = explode('-', $lastProfile->code);
                        $lastNumber = (int) end($parts);
                        $nextNumber = $lastNumber + 1;

                        Log::info('JobProfile generateCode - Último perfil de convocatoria', [
                            'last_code' => $lastProfile->code,
                            'last_number' => $lastNumber,
                            'next_number' => $nextNumber,
                        ]);
                    } else {
                        Log::info('JobProfile generateCode - Primer perfil de esta convocatoria');
                    }

                    $generatedCode = $jobPosting->code . '-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
                    Log::info('JobProfile generateCode - Código generado para convocatoria', [
                        'code' => $generatedCode,
                    ]);

                    return $generatedCode;
                }
            }
        }

        // Código independiente: obtener el último perfil del año con bloqueo pesimista
        Log::info('JobProfile generateCode - Generando código independiente');

        // Usar DB::table() para evitar global scopes y obtener todos los registros
        $lastProfile = DB::table('job_profiles')
            ->whereNull('job_posting_id')
            ->whereNull('deleted_at') // Considerar soft deletes manualmente
            ->whereYear('created_at', $year)
            ->where('code', 'like', 'PROF-' . $year . '-%')
            ->orderBy('code', 'desc')
            ->lockForUpdate()
            ->first();

        $nextNumber = 1;
        if ($lastProfile) {
            // Extraer el número del código (ej: "PROF-2025-010" -> 10)
            $parts = explode('-', $lastProfile->code);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;

            Log::info('JobProfile generateCode - Último perfil independiente', [
                'last_code' => $lastProfile->code,
                'last_number' => $lastNumber,
                'next_number' => $nextNumber,
            ]);
        } else {
            Log::info('JobProfile generateCode - Primer perfil del año');
        }

        $generatedCode = 'PROF-' . $year . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        Log::info('JobProfile generateCode - Código generado', [
            'code' => $generatedCode,
        ]);

        return $generatedCode;
    }
}
