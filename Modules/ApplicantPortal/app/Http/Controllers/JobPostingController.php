<?php

namespace Modules\ApplicantPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\JobPosting\Services\JobPostingService;
use Modules\Application\Services\ApplicationService;

class JobPostingController extends Controller
{
    public function __construct(
        protected JobPostingService $jobPostingService,
        protected ApplicationService $applicationService
    ) {}

    /**
     * Display a listing of active job postings.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $organizationalUnit = $request->get('organizational_unit');
        $educationLevel = $request->get('education_level');

        // Get active postings with filters
        $postings = $this->jobPostingService->getActivePostings([
            'search' => $search,
            'organizational_unit' => $organizationalUnit,
            'education_level' => $educationLevel,
        ]);

        // Get user's applications to mark already applied postings
        $user = Auth::user();
        $userApplications = $this->applicationService->getUserApplications($user->id);
        $appliedPostingIds = $userApplications->pluck('job_posting_id')->toArray();

        // Get filters data
        //$organizationalUnits = $this->jobPostingService->getOrganizationalUnitsWithPostings();
        //$educationLevels = $this->jobPostingService->getEducationLevels();

        return view('applicantportal::job-postings.index', compact(
            'postings',
            'appliedPostingIds',
            //'organizationalUnits',
            //'educationLevels',
            'search',
            'organizationalUnit',
            'educationLevel'
        ));
    }

    /**
     * Display the specified job posting with all details.
     */
    public function show(string $id)
    {
        $posting = $this->jobPostingService->getJobPostingById($id);

        // Get all job profiles (positions) for this posting
        $jobProfiles = $this->jobPostingService->getJobProfiles($id);

        // Check if user has already applied to any position in this posting
        $user = Auth::user();
        $userApplications = $this->applicationService->getUserApplicationsForPosting($user->id, $id);
        $hasApplied = $userApplications->count() > 0;

        // Get current phase
        $currentPhase = $this->jobPostingService->getCurrentPhase($id);

        return view('applicantportal::job-postings.show', compact(
            'posting',
            'jobProfiles',
            'hasApplied',
            'userApplications',
            'currentPhase'
        ));
    }

    /**
     * Show application form for a specific job profile.
     */
    public function apply(string $postingId, string $profileId)
    {
        $posting = $this->jobPostingService->getJobPostingById($postingId);
        $jobProfile = $this->jobPostingService->getJobProfileById($profileId);

        // Check if posting is in registration phase
        $currentPhase = $this->jobPostingService->getCurrentPhase($postingId);
        if ($currentPhase->phase_type !== 'REGISTRATION') {
            return redirect()
                ->route('applicant.job-postings.show', $postingId)
                ->with('error', 'Esta convocatoria no está en fase de registro.');
        }

        // Check if user has already applied to this profile
        $user = Auth::user();
        $existingApplication = $this->applicationService->getUserApplicationForProfile($user->id, $profileId);
        if ($existingApplication) {
            return redirect()
                ->route('applicant.job-postings.show', $postingId)
                ->with('error', 'Ya has postulado a este perfil.');
        }

        // Get available vacancies
        $availableVacancies = $this->jobPostingService->getAvailableVacancies($profileId);

        // Get required documents
        $requiredDocuments = $this->jobPostingService->getRequiredDocuments($profileId);

        return view('applicantportal::job-postings.apply', compact(
            'posting',
            'jobProfile',
            'availableVacancies',
            'requiredDocuments'
        ));
    }

    /**
     * Store a new application.
     */
    public function storeApplication(Request $request, string $postingId, string $profileId)
    {
        $user = Auth::user();

        try {
            $application = $this->applicationService->createApplication([
                'applicant_id' => $user->id,
                'job_profile_id' => $profileId,
                'job_posting_id' => $postingId,
                'vacancy_id' => $request->vacancy_id,
                'terms_accepted' => $request->terms_accepted,
                'special_conditions' => $request->special_conditions,
                'documents' => $request->file('documents'),
            ]);

            return redirect()
                ->route('applicant.applications.show', $application->id)
                ->with('success', '¡Tu postulación ha sido enviada exitosamente!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al enviar la postulación: ' . $e->getMessage());
        }
    }
}
