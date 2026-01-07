<?php

namespace Modules\ApplicantPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\JobPosting\Services\JobPostingService;
use Modules\Application\Services\ApplicationService;

class DashboardController extends Controller
{
    public function __construct(
        protected ApplicationService $applicationService,
        protected JobPostingService $jobPostingService,
    ) {}

    /**
     * Display the applicant dashboard with statistics and recent applications.
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's applications with statistics
        $myApplications = $this->applicationService->getUserApplications($user->id);
        $recentApplications = $myApplications->take(3);

        // Get active job postings
        $activePostings = $this->jobPostingService->getActivePostings();

        // Calculate statistics
        $stats = [
            'active_applications' => $myApplications->whereIn('status', [
                \Modules\Application\Enums\ApplicationStatus::SUBMITTED,
                \Modules\Application\Enums\ApplicationStatus::IN_REVIEW,
                \Modules\Application\Enums\ApplicationStatus::IN_EVALUATION
            ])->count(),
            'approved_applications' => $myApplications->where('status', \Modules\Application\Enums\ApplicationStatus::APPROVED)->count(),
            'in_evaluation' => $myApplications->where('status', \Modules\Application\Enums\ApplicationStatus::IN_EVALUATION)->count(),
            'available_postings' => $activePostings->count(),
        ];

        // Get profile completeness (mock for now, should be in UserService)
        //$profileCompleteness = $this->calculateProfileCompleteness($user);

        // Get upcoming dates (interviews, deadlines, etc.)
        //$upcomingDates = $this->applicationService->getUpcomingDates($user->id);

        return view('applicantportal::dashboard', compact(
            'user',
            'myApplications',
            'recentApplications',
            'activePostings',
            'stats',
        ));
    }

    /**
     * Calculate profile completeness percentage.
     */
    private function calculateProfileCompleteness($user): array
    {
        $completeness = [
            'total' => 0,
            'sections' => []
        ];

        // Personal data (always required)
        $personalComplete = !empty($user->first_name) && !empty($user->last_name) && !empty($user->dni) && !empty($user->email);
        $completeness['sections']['personal'] = [
            'name' => 'Datos personales',
            'percentage' => $personalComplete ? 100 : 0,
            'status' => $personalComplete ? 'complete' : 'incomplete'
        ];

        // Work experience (check if user has any)
        $workExperienceComplete = $user->workExperiences()->count() > 0;
        $completeness['sections']['work_experience'] = [
            'name' => 'Experiencia laboral',
            'percentage' => $workExperienceComplete ? 100 : 0,
            'status' => $workExperienceComplete ? 'complete' : 'incomplete'
        ];

        // Education (check if user has any)
        $educationComplete = $user->educations()->count() > 0;
        $completeness['sections']['education'] = [
            'name' => 'Formación académica',
            'percentage' => $educationComplete ? 100 : 70,
            'status' => $educationComplete ? 'complete' : 'partial'
        ];

        // Calculate total
        $completeness['total'] = round(
            ($completeness['sections']['personal']['percentage'] +
             $completeness['sections']['work_experience']['percentage'] +
             $completeness['sections']['education']['percentage']) / 3
        );

        return $completeness;
    }
}
