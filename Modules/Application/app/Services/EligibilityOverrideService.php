<?php

namespace Modules\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Application\Entities\Application;
use Modules\Application\Entities\ApplicationHistory;
use Modules\Application\Entities\EligibilityOverride;
use Modules\Application\Enums\ApplicationStatus;
use Modules\Application\Enums\OverrideDecisionEnum;

class EligibilityOverrideService
{
    /**
     * Obtener postulaciones que pueden ser reevaluadas
     * (NO_APTO o PENDIENTES de calificación, sin reclamo pendiente)
     */
    public function getApplicationsForReview(string $jobPostingId, ?string $jobProfileId = null, ?string $phaseId = null): Collection
    {
        return Application::whereHas('jobProfile', function ($q) use ($jobPostingId) {
                $q->where('job_posting_id', $jobPostingId);
            })
            ->when($jobProfileId, fn($q) => $q->where('job_profile_id', $jobProfileId))
            ->when($phaseId, function ($q) use ($phaseId) {
                // Filtrar por fase: obtener evaluaciones de esa fase específica
                $q->whereHas('evaluations', fn($eq) => $eq->where('phase_id', $phaseId));
            })
            ->where(function ($q) {
                $q->where('status', ApplicationStatus::NOT_ELIGIBLE)
                  ->orWhereIn('status', [
                      ApplicationStatus::SUBMITTED,
                      ApplicationStatus::IN_REVIEW
                  ]);
            })
            // Solo excluir si tiene un reclamo pendiente (sin resolver)
            ->whereDoesntHave('pendingEligibilityOverride')
            ->with(['applicant', 'jobProfile', 'latestEvaluation', 'evaluations.phase', 'eligibilityOverrides'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener postulaciones con override ya resuelto
     */
    public function getResolvedApplications(string $jobPostingId, ?string $jobProfileId = null, ?string $phaseId = null): Collection
    {
        $query = Application::whereHas('jobProfile', function ($q) use ($jobPostingId) {
                $q->where('job_posting_id', $jobPostingId);
            })
            ->when($jobProfileId, fn($q) => $q->where('job_profile_id', $jobProfileId))
            ->whereHas('eligibilityOverride')
            ->with(['applicant', 'jobProfile', 'eligibilityOverride.resolver', 'evaluations.phase']);

        // Si hay filtro por fase, solo retornar las aplicaciones que tienen un override
        // y que además tienen una evaluación MODIFICADA en esa fase específica
        // O que su última evaluación antes del override fue en esa fase
        if ($phaseId) {
            $query->where(function ($q) use ($phaseId) {
                // Opción 1: Tiene una evaluación MODIFICADA en esa fase (indica que el override afectó esa fase)
                $q->whereHas('evaluations', function ($eq) use ($phaseId) {
                    $eq->where('phase_id', $phaseId)
                       ->where('status', 'MODIFIED');
                })
                // Opción 2: O tiene metadata en eligibilityOverride que indica la fase
                ->orWhereHas('eligibilityOverride', function ($oq) use ($phaseId) {
                    $oq->whereJsonContains('metadata->affected_phase_id', $phaseId);
                });
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Aprobar postulación (cambiar a APTO)
     */
    public function approve(
        Application $application,
        string $resolutionSummary,
        string $resolutionDetail,
        string $resolvedBy,
        string $resolutionType = 'CLAIM',
        ?string $affectedPhaseId = null
    ): EligibilityOverride {
        return DB::transaction(function () use ($application, $resolutionSummary, $resolutionDetail, $resolvedBy, $resolutionType, $affectedPhaseId) {
            // Verificar que no tenga un override pendiente (sin resolver)
            if ($application->pendingEligibilityOverride) {
                throw new \Exception('Esta postulación ya tiene un reclamo pendiente de resolución');
            }

            $originalStatus = $application->status->value;

            // Preparar metadata
            $metadata = [];
            if ($affectedPhaseId) {
                $metadata['affected_phase_id'] = $affectedPhaseId;
            }

            // 1. Crear registro de override
            $override = EligibilityOverride::create([
                'application_id' => $application->id,
                'original_status' => $originalStatus,
                'original_reason' => $application->ineligibility_reason,
                'new_status' => ApplicationStatus::ELIGIBLE->value,
                'decision' => OverrideDecisionEnum::APPROVED,
                'resolution_type' => $resolutionType,
                'resolution_summary' => $resolutionSummary,
                'resolution_detail' => $resolutionDetail,
                'resolved_by' => $resolvedBy,
                'resolved_at' => now(),
                'metadata' => $metadata,
            ]);

            // 2. Actualizar Application
            $application->update([
                'is_eligible' => true,
                'status' => ApplicationStatus::ELIGIBLE,
                'ineligibility_reason' => null,
                'eligibility_checked_by' => $resolvedBy,
                'eligibility_checked_at' => now(),
            ]);

            // 3. Registrar en historial
            ApplicationHistory::log(
                $application->id,
                'ELIGIBILITY_OVERRIDE',
                [
                    'old_status' => $originalStatus,
                    'new_status' => ApplicationStatus::ELIGIBLE->value,
                    'metadata' => [
                        'override_id' => $override->id,
                        'decision' => 'APPROVED',
                        'resolution_type' => $resolutionType,
                    ],
                ],
                "Reevaluación: APROBADO - {$resolutionSummary}"
            );

            return $override;
        });
    }

    /**
     * Rechazar reevaluación (mantener NO_APTO)
     */
    public function reject(
        Application $application,
        string $resolutionSummary,
        string $resolutionDetail,
        string $resolvedBy,
        string $resolutionType = 'CLAIM',
        ?string $affectedPhaseId = null
    ): EligibilityOverride {
        return DB::transaction(function () use ($application, $resolutionSummary, $resolutionDetail, $resolvedBy, $resolutionType, $affectedPhaseId) {
            // Verificar que no tenga un override pendiente (sin resolver)
            if ($application->pendingEligibilityOverride) {
                throw new \Exception('Esta postulación ya tiene un reclamo pendiente de resolución');
            }

            $originalStatus = $application->status->value;

            // Preparar metadata
            $metadata = [];
            if ($affectedPhaseId) {
                $metadata['affected_phase_id'] = $affectedPhaseId;
            }

            // 1. Crear registro de override (sin cambiar a APTO)
            $override = EligibilityOverride::create([
                'application_id' => $application->id,
                'original_status' => $originalStatus,
                'original_reason' => $application->ineligibility_reason,
                'new_status' => ApplicationStatus::NOT_ELIGIBLE->value,
                'decision' => OverrideDecisionEnum::REJECTED,
                'resolution_type' => $resolutionType,
                'resolution_summary' => $resolutionSummary,
                'resolution_detail' => $resolutionDetail,
                'resolved_by' => $resolvedBy,
                'resolved_at' => now(),
                'metadata' => $metadata,
            ]);

            // 2. Si estaba PENDIENTE (no era NO_APTO), marcarlo como NO_APTO
            if ($application->status !== ApplicationStatus::NOT_ELIGIBLE) {
                $application->update([
                    'is_eligible' => false,
                    'status' => ApplicationStatus::NOT_ELIGIBLE,
                    'eligibility_checked_by' => $resolvedBy,
                    'eligibility_checked_at' => now(),
                ]);
            }

            // 3. Registrar en historial
            ApplicationHistory::log(
                $application->id,
                'ELIGIBILITY_OVERRIDE',
                [
                    'old_status' => $originalStatus,
                    'new_status' => ApplicationStatus::NOT_ELIGIBLE->value,
                    'metadata' => [
                        'override_id' => $override->id,
                        'decision' => 'REJECTED',
                        'resolution_type' => $resolutionType,
                    ],
                ],
                "Reevaluación: RECHAZADO - {$resolutionSummary}"
            );

            return $override;
        });
    }

    /**
     * Obtener detalle de una postulación para reevaluación
     */
    public function getApplicationDetail(Application $application): Application
    {
        return $application->load([
            'applicant',
            'jobProfile.jobPosting',
            'academics.career',
            'experiences',
            'trainings',
            'professionalRegistrations',
            'knowledge',
            'specialConditions',
            'latestEvaluation',
            'eligibilityOverride.resolver',
            'eligibilityOverrides.resolver',
            'pendingEligibilityOverride',
            'history' => fn($q) => $q->orderBy('performed_at', 'desc')->limit(10),
        ]);
    }

    /**
     * Verificar si una postulación puede ser reevaluada
     */
    public function canBeReviewed(Application $application): bool
    {
        // No puede tener un reclamo pendiente (sin resolver)
        if ($application->pendingEligibilityOverride) {
            return false;
        }

        // Solo estados específicos
        return in_array($application->status, [
            ApplicationStatus::NOT_ELIGIBLE,
            ApplicationStatus::SUBMITTED,
            ApplicationStatus::IN_REVIEW,
        ]);
    }

    /**
     * Obtener estadísticas de reevaluaciones por convocatoria
     */
    public function getStatistics(string $jobPostingId, ?string $phaseId = null, ?string $jobProfileId = null): array
    {
        $query = EligibilityOverride::whereHas('application.jobProfile', function ($q) use ($jobPostingId) {
            $q->where('job_posting_id', $jobPostingId);
        });

        // Filtrar por perfil si se proporciona
        if ($jobProfileId) {
            $query->whereHas('application', function ($q) use ($jobProfileId) {
                $q->where('job_profile_id', $jobProfileId);
            });
        }

        // Filtrar por fase si se proporciona (aplicar la misma lógica que getResolvedApplications)
        if ($phaseId) {
            $query->where(function ($q) use ($phaseId) {
                // Opción 1: Tiene una evaluación MODIFICADA en esa fase
                $q->whereHas('application.evaluations', function ($eq) use ($phaseId) {
                    $eq->where('phase_id', $phaseId)
                       ->where('status', 'MODIFIED');
                })
                // Opción 2: O tiene metadata que indica la fase afectada
                ->orWhere(function ($oq) use ($phaseId) {
                    $oq->whereJsonContains('metadata->affected_phase_id', $phaseId);
                });
            });
        }

        $overrides = $query->get();

        return [
            'total' => $overrides->count(),
            'approved' => $overrides->where('decision', OverrideDecisionEnum::APPROVED)->count(),
            'rejected' => $overrides->where('decision', OverrideDecisionEnum::REJECTED)->count(),
            'by_type' => [
                'claim' => $overrides->where('resolution_type', 'CLAIM')->count(),
                'correction' => $overrides->where('resolution_type', 'CORRECTION')->count(),
                'other' => $overrides->where('resolution_type', 'OTHER')->count(),
            ],
        ];
    }
}
