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
     * (NO_APTO sin override, o PENDIENTES de calificación)
     */
    public function getApplicationsForReview(string $jobPostingId, ?string $jobProfileId = null): Collection
    {
        return Application::whereHas('jobProfile', function ($q) use ($jobPostingId) {
                $q->where('job_posting_id', $jobPostingId);
            })
            ->when($jobProfileId, fn($q) => $q->where('job_profile_id', $jobProfileId))
            ->where(function ($q) {
                $q->where('status', ApplicationStatus::NOT_ELIGIBLE)
                  ->orWhereIn('status', [
                      ApplicationStatus::SUBMITTED,
                      ApplicationStatus::IN_REVIEW
                  ]);
            })
            ->whereDoesntHave('eligibilityOverride')
            ->with(['applicant', 'jobProfile', 'latestEvaluation'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener postulaciones con override ya resuelto
     */
    public function getResolvedApplications(string $jobPostingId, ?string $jobProfileId = null): Collection
    {
        return Application::whereHas('jobProfile', function ($q) use ($jobPostingId) {
                $q->where('job_posting_id', $jobPostingId);
            })
            ->when($jobProfileId, fn($q) => $q->where('job_profile_id', $jobProfileId))
            ->whereHas('eligibilityOverride')
            ->with(['applicant', 'jobProfile', 'eligibilityOverride.resolver'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Aprobar postulación (cambiar a APTO)
     */
    public function approve(
        Application $application,
        string $resolutionSummary,
        string $resolutionDetail,
        string $resolvedBy,
        string $resolutionType = 'CLAIM'
    ): EligibilityOverride {
        return DB::transaction(function () use ($application, $resolutionSummary, $resolutionDetail, $resolvedBy, $resolutionType) {
            // Verificar que no tenga override previo
            if ($application->eligibilityOverride) {
                throw new \Exception('Esta postulación ya tiene una resolución de reevaluación');
            }

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
        string $resolutionType = 'CLAIM'
    ): EligibilityOverride {
        return DB::transaction(function () use ($application, $resolutionSummary, $resolutionDetail, $resolvedBy, $resolutionType) {
            // Verificar que no tenga override previo
            if ($application->eligibilityOverride) {
                throw new \Exception('Esta postulación ya tiene una resolución de reevaluación');
            }

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
            'history' => fn($q) => $q->orderBy('performed_at', 'desc')->limit(10),
        ]);
    }

    /**
     * Verificar si una postulación puede ser reevaluada
     */
    public function canBeReviewed(Application $application): bool
    {
        // No puede tener override previo
        if ($application->eligibilityOverride) {
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
    public function getStatistics(string $jobPostingId): array
    {
        $overrides = EligibilityOverride::whereHas('application.jobProfile', function ($q) use ($jobPostingId) {
            $q->where('job_posting_id', $jobPostingId);
        })->get();

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
