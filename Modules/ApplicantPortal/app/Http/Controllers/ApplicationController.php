<?php

namespace Modules\ApplicantPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Application\Services\ApplicationService;
use Modules\Application\Entities\Application;
use Illuminate\Http\RedirectResponse;


class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationService $applicationService
    ) {}

    /**
     * Display a listing of the user's applications.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get filter parameters
        $status = $request->get('status');
        $search = $request->get('search');

        // Get applications with filters
        $applications = $this->applicationService->getUserApplications(
            $user->id,
            $status,
            $search
        );

        // Get status counts for filter badges
        $statusCounts = $this->applicationService->getUserApplicationStatusCounts($user->id);

        return view('applicantportal::applications.index', compact(
            'applications',
            'statusCounts',
            'status',
            'search'
        ));
    }

    /**
     * Display the specified application.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $application = $this->applicationService->getApplicationById($id);

        // Check if user owns this application
        if ($application->applicant_id !== $user->id) {
            abort(403, 'No tienes permiso para ver esta postulación.');
        }

        // Load necessary relationships
        $application->load([
            'jobProfile.jobPosting.schedules.phase',  // ← ACTUALIZADO: relación directa
            'assignedVacancy',  // ← ACTUALIZADO: vacante asignada si existe
            'academics',
            'experiences',
            'trainings',
            'specialConditions',
            'professionalRegistrations',
            'knowledge',
            'documents'
        ]);

        // Get related entities
        $jobProfile = $application->jobProfile;  // ← ACTUALIZADO
        $jobPosting = $jobProfile->jobPosting;

        // Get current phase using the method instead of a relationship
        $currentPhase = $jobPosting->getCurrentPhase();

        return view('applicantportal::applications.show', compact(
            'application',
            'jobProfile',
            'jobPosting',
            'currentPhase'
        ));
    }

    /**
     * Withdraw an application.
     */
    public function withdraw(string $id)
    {
        $user = Auth::user();
        $application = $this->applicationService->getApplicationById($id);

        // Check if user owns this application
        if ($application->applicant_id !== $user->id) {
            abort(403, 'No tienes permiso para desistir esta postulación.');
        }

        try {
            $this->applicationService->withdrawApplication($id, $user->id);

            return redirect()
                ->route('applicant.applications.index')
                ->with('success', 'Has desistido de tu postulación exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'No se pudo desistir de la postulación: ' . $e->getMessage());
        }
    }

    /**
     * Download application documents.
     */
    public function downloadDocument(string $id, string $documentId)
    {
        $user = Auth::user();
        $application = $this->applicationService->getApplicationById($id);

        // Check if user owns this application
        if ($application->applicant_id !== $user->id) {
            abort(403, 'No tienes permiso para descargar este documento.');
        }

        return $this->applicationService->downloadDocument($documentId);
    }

    public function submit(string $id): RedirectResponse
    {
        try {
            $application = $this->applicationService->submitApplication($id);

            return redirect()
                ->back()
                ->with('success', 'Postulacion enviada correctamente');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
