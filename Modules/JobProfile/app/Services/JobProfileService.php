<?php

namespace Modules\JobProfile\Services;

use Modules\Core\Services\BaseService;
use Modules\JobProfile\Entities\JobProfile;
use Modules\JobProfile\Repositories\JobProfileRepository;
use Modules\Core\Exceptions\BusinessRuleException;

class JobProfileService extends BaseService
{
    public function __construct(JobProfileRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data, array $requirements = [], array $responsibilities = []): JobProfile
    {
        return $this->transaction(function () use ($data, $requirements, $responsibilities) {
            $data['status'] = 'draft';
            $data['requested_at'] = now();

            $profile = $this->repository->create($data);

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

            return $profile->fresh(['requirements', 'responsibilities']);
        });
    }

    public function update(string $id, array $data): JobProfile
    {
        $profile = $this->repository->findOrFail($id);

        if ($profile->status === 'approved' || $profile->status === 'active') {
            throw new BusinessRuleException('No se puede modificar un perfil aprobado o activo.');
        }

        $this->repository->update($id, $data);
        return $this->repository->findOrFail($id);
    }

    public function submitForReview(string $id, string $requestedBy): JobProfile
    {
        $profile = $this->repository->findOrFail($id);

        if ($profile->status !== 'draft') {
            throw new BusinessRuleException('Solo se pueden enviar a revisión perfiles en borrador.');
        }

        $profile->update([
            'status' => 'pending_review',
            'requested_by' => $requestedBy,
            'requested_at' => now(),
        ]);

        return $profile->fresh();
    }

    public function approve(string $id, string $approvedBy, ?string $comments = null): JobProfile
    {
        $profile = $this->repository->findOrFail($id);

        if ($profile->status !== 'pending_review') {
            throw new BusinessRuleException('Solo se pueden aprobar perfiles en revisión.');
        }

        $profile->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        return $profile->fresh();
    }

    public function reject(string $id, string $reviewedBy, string $reason): JobProfile
    {
        $profile = $this->repository->findOrFail($id);

        if ($profile->status !== 'pending_review') {
            throw new BusinessRuleException('Solo se pueden rechazar perfiles en revisión.');
        }

        $profile->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
        ]);

        return $profile->fresh();
    }

    public function activate(string $id): JobProfile
    {
        $profile = $this->repository->findOrFail($id);

        if ($profile->status !== 'approved') {
            throw new BusinessRuleException('Solo se pueden activar perfiles aprobados.');
        }

        $profile->update(['status' => 'active']);
        return $profile->fresh();
    }

    public function deactivate(string $id): JobProfile
    {
        $profile = $this->repository->findOrFail($id);

        if ($profile->status !== 'active') {
            throw new BusinessRuleException('Solo se pueden desactivar perfiles activos.');
        }

        $profile->update(['status' => 'inactive']);
        return $profile->fresh();
    }
}
