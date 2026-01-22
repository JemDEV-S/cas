<?php

namespace Modules\Application\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Application\Entities\Application;
use Modules\Application\Services\EligibilityOverrideService;
use Modules\Application\Http\Requests\ApproveOverrideRequest;
use Modules\Application\Http\Requests\RejectOverrideRequest;
use Modules\Application\Http\Resources\EligibilityOverrideResource;
use Modules\JobPosting\Entities\JobPosting;

class EligibilityOverrideController extends Controller
{
    public function __construct(
        private EligibilityOverrideService $service
    ) {}

    /**
     * Mostrar lista de postulaciones para reevaluar
     */
    public function index(Request $request, string $postingId)
    {
        $this->authorize('eligibility.override');

        $posting = JobPosting::with(['jobProfiles'])->findOrFail($postingId);

        $jobProfileId = $request->get('job_profile_id');

        // Obtener postulaciones pendientes de revisión
        $pendingApplications = $this->service->getApplicationsForReview($postingId, $jobProfileId);

        // Obtener postulaciones ya resueltas
        $resolvedApplications = $this->service->getResolvedApplications($postingId, $jobProfileId);

        // Estadísticas
        $stats = $this->service->getStatistics($postingId);

        return view('application::admin.eligibility-override.index', compact(
            'posting',
            'pendingApplications',
            'resolvedApplications',
            'stats',
            'jobProfileId'
        ));
    }

    /**
     * Mostrar detalle de postulación para reevaluar
     */
    public function show(string $applicationId)
    {
        $this->authorize('eligibility.override');

        $application = Application::findOrFail($applicationId);
        $application = $this->service->getApplicationDetail($application);

        $canBeReviewed = $this->service->canBeReviewed($application);

        return view('application::admin.eligibility-override.show', compact(
            'application',
            'canBeReviewed'
        ));
    }

    /**
     * Aprobar reevaluación (cambiar a APTO)
     */
    public function approve(ApproveOverrideRequest $request, string $applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if (!$this->service->canBeReviewed($application)) {
            return redirect()
                ->back()
                ->with('error', 'Esta postulación no puede ser reevaluada');
        }

        try {
            $override = $this->service->approve(
                $application,
                $request->resolution_summary,
                $request->resolution_detail,
                auth()->id(),
                $request->resolution_type ?? 'CLAIM'
            );

            return redirect()
                ->route('admin.eligibility-override.show', $application->id)
                ->with('success', 'Reclamo aprobado. El postulante ahora está en estado APTO.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Rechazar reevaluación (mantener NO_APTO)
     */
    public function reject(RejectOverrideRequest $request, string $applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if (!$this->service->canBeReviewed($application)) {
            return redirect()
                ->back()
                ->with('error', 'Esta postulación no puede ser reevaluada');
        }

        try {
            $override = $this->service->reject(
                $application,
                $request->resolution_summary,
                $request->resolution_detail,
                auth()->id(),
                $request->resolution_type ?? 'CLAIM'
            );

            return redirect()
                ->route('admin.eligibility-override.show', $application->id)
                ->with('success', 'Reclamo rechazado. El postulante mantiene estado NO APTO.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Generar PDF de resolución
     */
    public function generatePdf(string $applicationId)
    {
        $this->authorize('eligibility.override');

        $application = Application::with([
            'applicant',
            'jobProfile.jobPosting',
            'eligibilityOverride.resolver'
        ])->findOrFail($applicationId);

        $override = $application->eligibilityOverride;

        if (!$override) {
            return redirect()
                ->back()
                ->with('error', 'No hay resolución de reevaluación para esta postulación');
        }

        $posting = $application->jobProfile->jobPosting;

        $pdf = Pdf::loadView('document::templates.eligibility_resolution', [
            'application' => $application,
            'override' => $override,
            'posting' => $posting,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = "resolucion-reclamo-{$application->code}.pdf";

        return $pdf->download($filename);
    }

    /**
     * API: Listar postulaciones para reevaluar
     */
    public function apiIndex(Request $request)
    {
        $this->authorize('eligibility.override');

        $applications = $this->service->getApplicationsForReview(
            $request->job_posting_id,
            $request->job_profile_id
        );

        return response()->json([
            'data' => $applications,
            'meta' => [
                'total' => $applications->count(),
            ]
        ]);
    }

    /**
     * API: Aprobar reevaluación
     */
    public function apiApprove(ApproveOverrideRequest $request, Application $application)
    {
        if (!$this->service->canBeReviewed($application)) {
            return response()->json([
                'error' => 'Esta postulación no puede ser reevaluada'
            ], 422);
        }

        try {
            $override = $this->service->approve(
                $application,
                $request->resolution_summary,
                $request->resolution_detail,
                auth()->id(),
                $request->resolution_type ?? 'CLAIM'
            );

            return new EligibilityOverrideResource($override->load('resolver', 'application'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * API: Rechazar reevaluación
     */
    public function apiReject(RejectOverrideRequest $request, Application $application)
    {
        if (!$this->service->canBeReviewed($application)) {
            return response()->json([
                'error' => 'Esta postulación no puede ser reevaluada'
            ], 422);
        }

        try {
            $override = $this->service->reject(
                $application,
                $request->resolution_summary,
                $request->resolution_detail,
                auth()->id(),
                $request->resolution_type ?? 'CLAIM'
            );

            return new EligibilityOverrideResource($override->load('resolver', 'application'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
