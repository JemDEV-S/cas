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
        $metadata = [];
        if ($affectedPhaseId) {
            $metadata['affected_phase_id'] = $affectedPhaseId;
        }

        return $this->approveWithMetadata(
            $application,
            $resolutionSummary,
            $resolutionDetail,
            $resolvedBy,
            $resolutionType,
            $metadata
        );
    }

    /**
     * Aprobar postulación con metadata personalizado
     */
    public function approveWithMetadata(
        Application $application,
        string $resolutionSummary,
        string $resolutionDetail,
        string $resolvedBy,
        string $resolutionType,
        array $metadata = []
    ): EligibilityOverride {
        return DB::transaction(function () use ($application, $resolutionSummary, $resolutionDetail, $resolvedBy, $resolutionType, $metadata) {
            $originalStatus = $application->status->value;

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
     * Aprobar reclamo de puntaje (postulante ya es APTO, solo se modifican calificaciones)
     */
    public function approveScoreClaim(
        Application $application,
        string $resolutionSummary,
        string $resolutionDetail,
        string $resolvedBy,
        array $metadata = []
    ): EligibilityOverride {
        return DB::transaction(function () use ($application, $resolutionSummary, $resolutionDetail, $resolvedBy, $metadata) {
            $originalStatus = $application->status->value;

            // 1. Crear registro de override
            $override = EligibilityOverride::create([
                'application_id' => $application->id,
                'original_status' => $originalStatus, // APTO
                'original_reason' => null,
                'new_status' => $originalStatus, // Mantiene APTO
                'decision' => OverrideDecisionEnum::APPROVED,
                'resolution_type' => 'SCORE_CLAIM',
                'resolution_summary' => $resolutionSummary,
                'resolution_detail' => $resolutionDetail,
                'resolved_by' => $resolvedBy,
                'resolved_at' => now(),
                'metadata' => $metadata,
            ]);

            // 2. No cambiar status de Application (ya es APTO)
            // Los puntajes ya fueron modificados en la evaluación

            // 3. Registrar en historial
            ApplicationHistory::log(
                $application->id,
                'SCORE_CLAIM_RESOLVED',
                [
                    'status' => $originalStatus,
                    'metadata' => [
                        'override_id' => $override->id,
                        'decision' => 'APPROVED',
                        'resolution_type' => 'SCORE_CLAIM',
                        'score_impact' => $metadata['score_impact'] ?? null,
                    ],
                ],
                "Reclamo de puntaje APROBADO - {$resolutionSummary}"
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
        $metadata = [];
        if ($affectedPhaseId) {
            $metadata['affected_phase_id'] = $affectedPhaseId;
        }

        return $this->rejectWithMetadata(
            $application,
            $resolutionSummary,
            $resolutionDetail,
            $resolvedBy,
            $resolutionType,
            $metadata
        );
    }

    /**
     * Rechazar reevaluación con metadata personalizado
     */
    public function rejectWithMetadata(
        Application $application,
        string $resolutionSummary,
        string $resolutionDetail,
        string $resolvedBy,
        string $resolutionType,
        array $metadata = []
    ): EligibilityOverride {
        return DB::transaction(function () use ($application, $resolutionSummary, $resolutionDetail, $resolvedBy, $resolutionType, $metadata) {
            $originalStatus = $application->status->value;

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
     * Rechazar reclamo de puntaje (postulante APTO, no se modifican calificaciones)
     */
    public function rejectScoreClaim(
        Application $application,
        string $resolutionSummary,
        string $resolutionDetail,
        string $resolvedBy,
        array $metadata = []
    ): EligibilityOverride {
        return DB::transaction(function () use ($application, $resolutionSummary, $resolutionDetail, $resolvedBy, $metadata) {
            $originalStatus = $application->status->value;

            // 1. Crear registro de override
            $override = EligibilityOverride::create([
                'application_id' => $application->id,
                'original_status' => $originalStatus, // APTO
                'original_reason' => null,
                'new_status' => $originalStatus, // Mantiene APTO
                'decision' => OverrideDecisionEnum::REJECTED,
                'resolution_type' => 'SCORE_CLAIM',
                'resolution_summary' => $resolutionSummary,
                'resolution_detail' => $resolutionDetail,
                'resolved_by' => $resolvedBy,
                'resolved_at' => now(),
                'metadata' => $metadata,
            ]);

            // 2. No cambiar status de Application (mantiene APTO)
            // No se modifican los puntajes (se mantienen originales)

            // 3. Registrar en historial
            ApplicationHistory::log(
                $application->id,
                'SCORE_CLAIM_RESOLVED',
                [
                    'status' => $originalStatus,
                    'metadata' => [
                        'override_id' => $override->id,
                        'decision' => 'REJECTED',
                        'resolution_type' => 'SCORE_CLAIM',
                    ],
                ],
                "Reclamo de puntaje RECHAZADO - {$resolutionSummary}"
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
        // Contar aplicaciones resueltas usando la misma lógica que getResolvedApplications
        $resolvedQuery = Application::whereHas('jobProfile', function ($q) use ($jobPostingId) {
                $q->where('job_posting_id', $jobPostingId);
            })
            ->when($jobProfileId, fn($q) => $q->where('job_profile_id', $jobProfileId))
            ->whereHas('eligibilityOverride')
            ->with('eligibilityOverride');

        // Aplicar el mismo filtro de fase que en getResolvedApplications
        if ($phaseId) {
            $resolvedQuery->where(function ($q) use ($phaseId) {
                $q->whereHas('evaluations', function ($eq) use ($phaseId) {
                    $eq->where('phase_id', $phaseId)
                       ->where('status', 'MODIFIED');
                })
                ->orWhereHas('eligibilityOverride', function ($oq) use ($phaseId) {
                    $oq->whereJsonContains('metadata->affected_phase_id', $phaseId);
                });
            });
        }

        $resolvedApplications = $resolvedQuery->get();

        $approved = 0;
        $rejected = 0;

        foreach ($resolvedApplications as $application) {
            if ($application->eligibilityOverride->decision === OverrideDecisionEnum::APPROVED) {
                $approved++;
            } else {
                $rejected++;
            }
        }

        return [
            'total' => $resolvedApplications->count(),
            'approved' => $approved,
            'rejected' => $rejected,
            'by_type' => [
                'claim' => $resolvedApplications->filter(fn($app) => $app->eligibilityOverride->resolution_type === 'CLAIM')->count(),
                'score_claim' => $resolvedApplications->filter(fn($app) => $app->eligibilityOverride->resolution_type === 'SCORE_CLAIM')->count(),
                'correction' => $resolvedApplications->filter(fn($app) => $app->eligibilityOverride->resolution_type === 'CORRECTION')->count(),
                'score_correction' => $resolvedApplications->filter(fn($app) => $app->eligibilityOverride->resolution_type === 'SCORE_CORRECTION')->count(),
                'other' => $resolvedApplications->filter(fn($app) => $app->eligibilityOverride->resolution_type === 'OTHER')->count(),
            ],
        ];
    }
}
