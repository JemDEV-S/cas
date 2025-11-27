<?php

namespace Modules\JobProfile\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Exceptions\BusinessRuleException;
use Modules\JobProfile\Entities\JobProfile;
use Modules\JobProfile\Events\JobProfileApproved;
use Modules\JobProfile\Events\ProfileInReview;
use Modules\JobProfile\Events\ProfileModificationRequested;
use Modules\JobProfile\Events\ProfileRejected;
use Modules\JobProfile\Repositories\JobProfileRepository;

class ReviewService
{
    public function __construct(
        protected JobProfileRepository $repository
    ) {}

    /**
     * Envía un perfil a revisión
     */
    public function submitForReview(string $jobProfileId, string $userId): JobProfile
    {
        return DB::transaction(function () use ($jobProfileId, $userId) {
            $jobProfile = $this->repository->findOrFail($jobProfileId);

            // Validar que esté en estado correcto
            if (!$jobProfile->canSubmitForReview()) {
                throw new BusinessRuleException(
                    'Solo se pueden enviar a revisión perfiles en borrador o con modificación requerida.'
                );
            }

            // Validar que tenga datos mínimos requeridos
            $this->validateMinimumRequirements($jobProfile);

            // Actualizar estado
            $jobProfile->update([
                'status' => 'in_review',
                'requested_by' => $userId,
                'requested_at' => now(),
                // Limpiar campos de revisión anterior
                'review_comments' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);

            // Disparar evento
            event(new ProfileInReview($jobProfile, $userId));

            return $jobProfile->fresh();
        });
    }

    /**
     * Solicita modificaciones a un perfil
     */
    public function requestModification(string $jobProfileId, string $reviewerId, string $comments): JobProfile
    {
        return DB::transaction(function () use ($jobProfileId, $reviewerId, $comments) {
            $jobProfile = $this->repository->findOrFail($jobProfileId);

            // Validar que esté en revisión
            if (!$jobProfile->canRequestModification()) {
                throw new BusinessRuleException('Solo se pueden solicitar modificaciones a perfiles en revisión.');
            }

            // Actualizar estado
            $jobProfile->update([
                'status' => 'modification_requested',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'review_comments' => $comments,
            ]);

            // Disparar evento
            event(new ProfileModificationRequested($jobProfile, $reviewerId, $comments));

            return $jobProfile->fresh();
        });
    }

    /**
     * Aprueba un perfil
     */
    public function approve(string $jobProfileId, string $approverId, ?string $comments = null): JobProfile
    {
        return DB::transaction(function () use ($jobProfileId, $approverId, $comments) {
            $jobProfile = $this->repository->findOrFail($jobProfileId);

            // Validar que esté en revisión
            if (!$jobProfile->canApprove()) {
                throw new BusinessRuleException('Solo se pueden aprobar perfiles en revisión.');
            }

            // Validar requisitos completos para aprobación
            $this->validateForApproval($jobProfile);

            // Actualizar estado
            $jobProfile->update([
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
                'reviewed_by' => $approverId,
                'reviewed_at' => now(),
                'review_comments' => $comments,
            ]);

            // Disparar evento (esto activará la generación automática de vacantes)
            event(new JobProfileApproved($jobProfile, $approverId));

            return $jobProfile->fresh();
        });
    }

    /**
     * Rechaza un perfil
     */
    public function reject(string $jobProfileId, string $reviewerId, string $reason): JobProfile
    {
        return DB::transaction(function () use ($jobProfileId, $reviewerId, $reason) {
            $jobProfile = $this->repository->findOrFail($jobProfileId);

            // Validar que esté en revisión
            if (!$jobProfile->canReject()) {
                throw new BusinessRuleException('Solo se pueden rechazar perfiles en revisión.');
            }

            if (empty($reason)) {
                throw new BusinessRuleException('Debe proporcionar una razón para el rechazo.');
            }

            // Actualizar estado
            $jobProfile->update([
                'status' => 'rejected',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            // Disparar evento
            event(new ProfileRejected($jobProfile, $reviewerId, $reason));

            return $jobProfile->fresh();
        });
    }

    /**
     * Activa un perfil aprobado
     */
    public function activate(string $jobProfileId): JobProfile
    {
        return DB::transaction(function () use ($jobProfileId) {
            $jobProfile = $this->repository->findOrFail($jobProfileId);

            if (!$jobProfile->isApproved()) {
                throw new BusinessRuleException('Solo se pueden activar perfiles aprobados.');
            }

            $jobProfile->update(['status' => 'active']);

            return $jobProfile->fresh();
        });
    }

    /**
     * Desactiva un perfil activo
     */
    public function deactivate(string $jobProfileId): JobProfile
    {
        return DB::transaction(function () use ($jobProfileId) {
            $jobProfile = $this->repository->findOrFail($jobProfileId);

            if (!$jobProfile->isActive()) {
                throw new BusinessRuleException('Solo se pueden desactivar perfiles activos.');
            }

            $jobProfile->update(['status' => 'inactive']);

            return $jobProfile->fresh();
        });
    }

    /**
     * Valida requisitos mínimos para enviar a revisión
     */
    protected function validateMinimumRequirements(JobProfile $jobProfile): void
    {
        $errors = [];

        if (empty($jobProfile->title)) {
            $errors[] = 'El título del perfil es obligatorio';
        }

        if (empty($jobProfile->position_code_id)) {
            $errors[] = 'Debe seleccionar un código de posición';
        }

        if (empty($jobProfile->education_level)) {
            $errors[] = 'El nivel educativo es obligatorio';
        }

        if (empty($jobProfile->work_regime)) {
            $errors[] = 'El régimen laboral es obligatorio';
        }

        if (($jobProfile->total_vacancies ?? 0) < 1) {
            $errors[] = 'Debe especificar al menos una vacante';
        }

        if (!empty($errors)) {
            throw new BusinessRuleException(
                'El perfil no cumple con los requisitos mínimos: ' . implode(', ', $errors)
            );
        }
    }

    /**
     * Valida requisitos completos para aprobación
     */
    protected function validateForApproval(JobProfile $jobProfile): void
    {
        $this->validateMinimumRequirements($jobProfile);

        $errors = [];

        if (empty($jobProfile->description)) {
            $errors[] = 'La descripción del perfil es obligatoria';
        }

        if (empty($jobProfile->justification)) {
            $errors[] = 'La justificación del requerimiento es obligatoria';
        }

        if (empty($jobProfile->main_functions) || count($jobProfile->main_functions) === 0) {
            $errors[] = 'Debe especificar al menos una función principal';
        }

        if (!empty($errors)) {
            throw new BusinessRuleException(
                'El perfil no cumple con los requisitos para aprobación: ' . implode(', ', $errors)
            );
        }
    }
}
