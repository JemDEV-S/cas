<?php

namespace Modules\Results\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Results\Entities\ResultPublication;
use Modules\Results\Enums\PublicationStatusEnum;
use Modules\Application\Entities\Application;

class MyResultsController extends Controller
{
    /**
     * Ver mis resultados (portal del postulante)
     */
    public function index(Request $request)
    {
        $applicantId = auth()->id();

        // Obtener todas las postulaciones del postulante
        $applications = Application::where('applicant_id', $applicantId)
            ->with(['vacancy.jobProfile.jobPosting'])
            ->get();

        $results = [];

        foreach ($applications as $application) {
            $posting = $application->vacancy?->jobProfile?->jobPosting;

            if (!$posting) {
                continue;
            }

            // Obtener publicaciones de resultados para esta convocatoria
            $publications = ResultPublication::where('job_posting_id', $posting->id)
                ->where('status', PublicationStatusEnum::PUBLISHED)
                ->with(['document'])
                ->get();

            if ($publications->isNotEmpty()) {
                $results[] = [
                    'application' => $application,
                    'posting' => $posting,
                    'publications' => $publications,
                ];
            }
        }

        return view('results::applicant.my-results', compact('results'));
    }

    /**
     * Ver detalle de un resultado específico
     */
    public function show(ResultPublication $publication)
    {
        // Verificar que la publicación esté publicada
        if ($publication->status !== PublicationStatusEnum::PUBLISHED) {
            abort(404, 'Resultado no disponible');
        }

        // Verificar que el usuario tenga una postulación en esta convocatoria
        $application = Application::where('applicant_id', auth()->id())
            ->whereHas('vacancy.jobProfile.jobPosting', fn($q) =>
                $q->where('id', $publication->job_posting_id)
            )
            ->with(['vacancy.jobProfile'])
            ->first();

        if (!$application) {
            abort(403, 'No tienes una postulación en esta convocatoria');
        }

        $publication->load(['document', 'jobPosting']);

        // Preparar datos según la fase
        $resultData = $this->prepareResultData($publication, $application);

        return view('results::applicant.show-result', compact('publication', 'application', 'resultData'));
    }

    /**
     * Descargar PDF de resultados (solo si está publicado)
     */
    public function downloadPdf(ResultPublication $publication)
    {
        // Verificar que esté publicado
        if ($publication->status !== PublicationStatusEnum::PUBLISHED) {
            abort(404, 'Resultado no disponible');
        }

        // Verificar que el usuario tenga una postulación
        $hasApplication = Application::where('applicant_id', auth()->id())
            ->whereHas('vacancy.jobProfile.jobPosting', fn($q) =>
                $q->where('id', $publication->job_posting_id)
            )
            ->exists();

        if (!$hasApplication) {
            abort(403, 'No autorizado');
        }

        if (!$publication->document || !$publication->document->signed_pdf_path) {
            abort(404, 'PDF no disponible');
        }

        $filePath = storage_path('app/public/' . $publication->document->signed_pdf_path);

        if (!file_exists($filePath)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->download($filePath);
    }

    /**
     * Preparar datos del resultado según la fase
     */
    private function prepareResultData(ResultPublication $publication, Application $application): array
    {
        return match($publication->phase->value) {
            'PHASE_04' => [
                'result' => $application->is_eligible ? 'APTO' : 'NO APTO',
                'result_class' => $application->is_eligible ? 'success' : 'danger',
                'reason' => $application->ineligibility_reason,
                'checked_at' => $application->eligibility_checked_at,
            ],
            'PHASE_07' => [
                'ranking' => $application->ranking ?? 'N/A',
                'curriculum_score' => $application->curriculum_score ?? 0,
                'max_score' => 100,
            ],
            'PHASE_09' => [
                'ranking' => $application->final_ranking ?? 'N/A',
                'curriculum_score' => $application->curriculum_score ?? 0,
                'interview_score' => $application->interview_score ?? 0,
                'bonus' => $application->special_condition_bonus ?? 0,
                'final_score' => $application->final_score ?? 0,
                'is_winner' => $application->final_ranking === 1,
            ],
            default => [],
        };
    }
}
