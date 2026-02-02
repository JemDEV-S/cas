<?php

namespace Modules\Results\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Results\Services\InterviewResultProcessingService;
use Modules\Document\Services\InterviewEvaluationReportService;
use Modules\JobPosting\Entities\JobPosting;

class InterviewResultProcessingController extends Controller
{
    public function __construct(
        private InterviewResultProcessingService $processingService,
        private InterviewEvaluationReportService $reportService
    ) {}

    /**
     * Vista principal de procesamiento para una convocatoria específica
     */
    public function index(JobPosting $posting)
    {
        $summary = $this->processingService->getSummary($posting);

        return view('results::admin.interview-processing.index', compact('posting', 'summary'));
    }

    /**
     * Previsualizacion (dry-run)
     */
    public function preview(Request $request, JobPosting $posting)
    {
        $preview = $this->processingService->preview($posting);

        return view('results::admin.interview-processing.preview', compact('posting', 'preview'));
    }

    /**
     * Ejecutar procesamiento
     */
    public function execute(Request $request, JobPosting $posting)
    {
        try {
            $result = $this->processingService->execute($posting);

            return redirect()
                ->route('admin.results.interview-processing', $posting)
                ->with('success', "Procesamiento completado: {$result['processed']} entrevistas procesadas. Aprobados: {$result['passed']}, No aptos: {$result['failed']}");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error en procesamiento: ' . $e->getMessage());
        }
    }

    /**
     * Descargar PDF con resultados de evaluación de entrevista
     */
    public function downloadPdf(JobPosting $posting)
    {
        try {
            return $this->reportService->downloadPdf($posting);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }
}
