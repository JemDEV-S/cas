<?php

namespace Modules\Results\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Results\Services\CvResultProcessingService;
use Modules\Document\Services\CvEvaluationReportService;
use Modules\JobPosting\Entities\JobPosting;

class CvResultProcessingController extends Controller
{
    public function __construct(
        private CvResultProcessingService $processingService,
        private CvEvaluationReportService $reportService
    ) {}

    /**
     * Lista de convocatorias disponibles para procesar
     */
    public function list()
    {
        // Obtener convocatorias activas o en evaluación
        $postings = JobPosting::with(['jobProfiles'])
            ->whereIn('status', ['PUBLICADA', 'EN_EVALUACION', 'FINALIZADA'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('results::index', compact('postings'));
    }

    /**
     * Vista principal de procesamiento para una convocatoria específica
     */
    public function index(JobPosting $posting)
    {
        $summary = $this->processingService->getSummary($posting);

        return view('results::admin.cv-processing.index', compact('posting', 'summary'));
    }

    /**
     * Previsualizacion (dry-run)
     */
    public function preview(Request $request, JobPosting $posting)
    {
        $preview = $this->processingService->preview($posting);

        return view('results::admin.cv-processing.preview', compact('posting', 'preview'));
    }

    /**
     * Ejecutar procesamiento
     */
    public function execute(Request $request, JobPosting $posting)
    {
        try {
            $result = $this->processingService->execute($posting);

            return redirect()
                ->route('admin.results.cv-processing', $posting)
                ->with('success', "Procesamiento completado: {$result['processed']} postulaciones actualizadas.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error en procesamiento: ' . $e->getMessage());
        }
    }

    /**
     * Descargar PDF con resultados de evaluación CV
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
