<?php

namespace Modules\Results\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Results\Services\FinalScoreCalculationService;
use Modules\JobPosting\Entities\JobPosting;

class FinalResultCalculationController extends Controller
{
    public function __construct(
        private FinalScoreCalculationService $calculationService
    ) {}

    /**
     * Vista principal de cálculo de puntaje final
     */
    public function index(JobPosting $posting)
    {
        $summary = $this->calculationService->getSummary($posting);

        return view('results::admin.final-calculation.index', compact('posting', 'summary'));
    }

    /**
     * Previsualizacion (dry-run)
     */
    public function preview(Request $request, JobPosting $posting)
    {
        $preview = $this->calculationService->preview($posting);

        return view('results::admin.final-calculation.preview', compact('posting', 'preview'));
    }

    /**
     * Previsualizacion detallada organizada por unidad/perfil (dry-run)
     */
    public function previewDetailed(Request $request, JobPosting $posting)
    {
        $preview = $this->calculationService->preview($posting);

        return view('results::admin.final-calculation.preview-detailed', compact('posting', 'preview'));
    }

    /**
     * Ejecutar cálculo
     */
    public function execute(Request $request, JobPosting $posting)
    {
        try {
            $result = $this->calculationService->execute($posting);

            return redirect()
                ->route('admin.results.final-calculation', $posting)
                ->with('success', "Cálculo completado: {$result['processed']} postulaciones procesadas. Aprobados: {$result['approved']}, No aptos: {$result['failed']}");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error en cálculo: ' . $e->getMessage());
        }
    }
}
