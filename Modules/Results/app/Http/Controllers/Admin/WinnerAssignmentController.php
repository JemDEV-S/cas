<?php

namespace Modules\Results\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Results\Services\WinnerAssignmentService;
use Modules\Document\Services\WinnerAssignmentReportService;
use Modules\JobPosting\Entities\JobPosting;
use Modules\Results\app\Exports\WinnersExport;
use Maatwebsite\Excel\Facades\Excel;

class WinnerAssignmentController extends Controller
{
    public function __construct(
        private WinnerAssignmentService $assignmentService,
        private WinnerAssignmentReportService $reportService
    ) {}

    /**
     * Vista principal de asignación de ganadores
     */
    public function index(JobPosting $posting)
    {
        $summary = $this->assignmentService->getSummary($posting);

        return view('results::admin.winner-assignment.index', compact('posting', 'summary'));
    }

    /**
     * Previsualizacion (dry-run)
     */
    public function preview(Request $request, JobPosting $posting)
    {
        $accesitariosCount = (int) $request->get('accesitarios_count', WinnerAssignmentService::DEFAULT_ACCESITARIOS);
        $preview = $this->assignmentService->preview($posting, $accesitariosCount);

        return view('results::admin.winner-assignment.preview', compact('posting', 'preview'));
    }

    /**
     * Ejecutar asignación
     */
    public function execute(Request $request, JobPosting $posting)
    {
        try {
            $accesitariosCount = (int) $request->get('accesitarios_count', WinnerAssignmentService::DEFAULT_ACCESITARIOS);
            $result = $this->assignmentService->execute($posting, $accesitariosCount);

            return redirect()
                ->route('admin.results.winner-assignment', $posting)
                ->with('success', "Asignación completada: {$result['winners']} ganadores, {$result['accesitarios']} accesitarios, {$result['not_selected']} no seleccionados");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error en asignación: ' . $e->getMessage());
        }
    }

    /**
     * Descargar PDF con cuadro de méritos (resultados finales)
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

    /**
     * Exportar ganadores y accesitarios a Excel
     */
    public function exportWinners(JobPosting $posting)
    {
        try {
            $fileName = 'Ganadores_' . $posting->code . '_' . now()->format('Y-m-d_His') . '.xlsx';

            return Excel::download(new WinnersExport($posting), $fileName);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al generar Excel: ' . $e->getMessage());
        }
    }
}
