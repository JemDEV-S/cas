<?php

namespace Modules\JobProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\JobProfile\Services\VacancyService;
use Modules\JobProfile\Services\JobProfileService;
use Modules\Core\Exceptions\BusinessRuleException;

class VacancyController extends Controller
{
    public function __construct(
        protected VacancyService $vacancyService,
        protected JobProfileService $jobProfileService
    ) {}

    public function index(string $jobProfileId): View
    {
        $jobProfile = $this->jobProfileService->findById($jobProfileId);

        if (!$jobProfile) {
            abort(404, 'Perfil de puesto no encontrado.');
        }

        $vacancies = $jobProfile->vacancies;
        $statistics = $this->vacancyService->getVacancyStatistics($jobProfileId);

        return view('jobprofile::vacancies.index', compact('jobProfile', 'vacancies', 'statistics'));
    }

    public function generate(string $jobProfileId): RedirectResponse
    {
        try {
            $jobProfile = $this->jobProfileService->findById($jobProfileId);
            $this->vacancyService->generateVacancies($jobProfile);

            return back()->with('success', 'Vacantes generadas exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function declareVacant(Request $request, string $vacancyId): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|min:10']);

        try {
            $this->vacancyService->declareVacant($vacancyId, $request->reason);
            return back()->with('success', 'Vacante declarada desierta.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
