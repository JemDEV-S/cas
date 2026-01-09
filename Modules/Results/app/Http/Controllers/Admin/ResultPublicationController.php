<?php

namespace Modules\Results\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Results\Services\ResultPublicationService;
use Modules\Results\Services\ResultExportService;
use Modules\Results\Entities\ResultPublication;
use Modules\Results\Enums\PublicationPhaseEnum;
use Modules\Results\Enums\PublicationStatusEnum;
use Modules\Application\Entities\Application;
use Modules\JobPosting\Entities\JobPosting;
use Illuminate\Support\Facades\DB;

class ResultPublicationController extends Controller
{
    public function __construct(
        private ResultPublicationService $publicationService,
        private ResultExportService $exportService
    ) {}

    /**
     * Mostrar dashboard de publicaciones de resultados
     */
    public function index(Request $request)
    {
        // TODO: Agregar autorización
        // $this->authorize('viewAny', ResultPublication::class);

        $publications = ResultPublication::with([
                'jobPosting',
                'document',
                'publisher'
            ])
            ->when($request->phase, fn($q) => $q->forPhase(PublicationPhaseEnum::from($request->phase)))
            ->when($request->status, fn($q) => $q->where('status', PublicationStatusEnum::from($request->status)))
            ->latest()
            ->paginate(20);

        return view('results::admin.publications.index', compact('publications'));
    }

    /**
     * Mostrar detalle de una publicación
     */
    public function show(ResultPublication $publication)
    {
        // TODO: Agregar autorización
        // $this->authorize('view', $publication);

        $publication->load([
            'jobPosting',
            'document.signatures.user',
            'publisher',
            'unpublisher'
        ]);

        $signatureProgress = $publication->getSignatureProgress();

        return view('results::admin.publications.show', compact('publication', 'signatureProgress'));
    }

    /**
     * Formulario para publicar resultados de Fase 4
     */
    public function createPhase4(JobPosting $posting)
    {
        // TODO: Agregar autorización
        // $this->authorize('publishResults', Application::class);

        // Verificar que tenga postulaciones evaluadas
        $evaluatedCount = Application::whereHas('vacancy.jobProfile.jobPosting',
                fn($q) => $q->where('id', $posting->id)
            )
            ->whereNotNull('eligibility_checked_at')
            ->count();

        if ($evaluatedCount === 0) {
            return redirect()->back()->with('error', 'No hay postulaciones evaluadas para publicar');
        }

        // Obtener jurados disponibles (usuarios con rol de jurado)
        $jurors = \App\Models\User::whereHas('roles', fn($q) =>
            $q->where('name', 'Jurado Titular')
        )->get();

        return view('results::admin.publications.create-phase4', compact('posting', 'jurors', 'evaluatedCount'));
    }

    /**
     * Publicar resultados de Fase 4
     */
    public function storePhase4(Request $request, JobPosting $posting)
    {
        // TODO: Agregar autorización
        // $this->authorize('publishResults', Application::class);

        $validated = $request->validate([
            'jury_signers' => 'required|array|min:1',
            'jury_signers.*.user_id' => 'required|exists:users,id',
            'jury_signers.*.role' => 'required|string|max:100',
            'signature_mode' => 'required|in:sequential,parallel',
            'send_notifications' => 'boolean',
        ]);

        try {
            $publication = $this->publicationService->publishPhase4Results(
                $posting,
                $validated['jury_signers'],
                $validated['signature_mode'],
                $validated['send_notifications'] ?? true
            );

            return redirect()
                ->route('admin.results.show', $publication)
                ->with('success', 'Publicación de resultados iniciada. El documento está siendo firmado por los jurados.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al publicar resultados: ' . $e->getMessage());
        }
    }

    /**
     * Formulario para publicar resultados de Fase 7
     */
    public function createPhase7(JobPosting $posting)
    {
        // TODO: Agregar autorización

        $evaluatedCount = Application::whereHas('vacancy.jobProfile.jobPosting',
                fn($q) => $q->where('id', $posting->id)
            )
            ->where('is_eligible', true)
            ->whereNotNull('curriculum_score')
            ->count();

        if ($evaluatedCount === 0) {
            return redirect()->back()->with('error', 'No hay evaluaciones curriculares para publicar');
        }

        $jurors = \App\Models\User::whereHas('roles', fn($q) =>
            $q->where('name', 'Jurado Titular')
        )->get();

        return view('results::admin.publications.create-phase7', compact('posting', 'jurors', 'evaluatedCount'));
    }

    /**
     * Publicar resultados de Fase 7
     */
    public function storePhase7(Request $request, JobPosting $posting)
    {
        $validated = $request->validate([
            'jury_signers' => 'required|array|min:1',
            'jury_signers.*.user_id' => 'required|exists:users,id',
            'jury_signers.*.role' => 'required|string|max:100',
            'signature_mode' => 'required|in:sequential,parallel',
        ]);

        try {
            $publication = $this->publicationService->publishPhase7Results(
                $posting,
                $validated['jury_signers'],
                $validated['signature_mode']
            );

            return redirect()
                ->route('admin.results.show', $publication)
                ->with('success', 'Publicación de resultados curriculares iniciada.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al publicar resultados: ' . $e->getMessage());
        }
    }

    /**
     * Formulario para publicar resultados de Fase 9
     */
    public function createPhase9(JobPosting $posting)
    {
        $evaluatedCount = Application::whereHas('vacancy.jobProfile.jobPosting',
                fn($q) => $q->where('id', $posting->id)
            )
            ->where('is_eligible', true)
            ->whereNotNull('final_score')
            ->count();

        if ($evaluatedCount === 0) {
            return redirect()->back()->with('error', 'No hay resultados finales para publicar');
        }

        $jurors = \App\Models\User::whereHas('roles', fn($q) =>
            $q->where('name', 'Jurado Titular')
        )->get();

        return view('results::admin.publications.create-phase9', compact('posting', 'jurors', 'evaluatedCount'));
    }

    /**
     * Publicar resultados de Fase 9
     */
    public function storePhase9(Request $request, JobPosting $posting)
    {
        $validated = $request->validate([
            'jury_signers' => 'required|array|min:1',
            'jury_signers.*.user_id' => 'required|exists:users,id',
            'jury_signers.*.role' => 'required|string|max:100',
            'signature_mode' => 'required|in:sequential,parallel',
        ]);

        try {
            $publication = $this->publicationService->publishPhase9Results(
                $posting,
                $validated['jury_signers'],
                $validated['signature_mode']
            );

            return redirect()
                ->route('admin.results.show', $publication)
                ->with('success', 'Publicación de resultados finales iniciada.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al publicar resultados: ' . $e->getMessage());
        }
    }

    /**
     * Despublicar resultados
     */
    public function unpublish(ResultPublication $publication)
    {
        // TODO: Agregar autorización

        if (!$publication->canBeUnpublished()) {
            return redirect()
                ->back()
                ->with('error', 'No se puede despublicar un documento que ya tiene firmas');
        }

        try {
            $this->publicationService->unpublishResults($publication);

            return redirect()
                ->back()
                ->with('success', 'Publicación despublicada exitosamente');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al despublicar: ' . $e->getMessage());
        }
    }

    /**
     * Re-publicar resultados
     */
    public function republish(ResultPublication $publication)
    {
        // TODO: Agregar autorización

        if (!$publication->canBeRepublished()) {
            return redirect()
                ->back()
                ->with('error', 'El documento debe estar firmado para republicar');
        }

        try {
            $this->publicationService->republishResults($publication);

            return redirect()
                ->back()
                ->with('success', 'Publicación republicada exitosamente');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al republicar: ' . $e->getMessage());
        }
    }

    /**
     * Descargar PDF firmado
     */
    public function downloadPdf(ResultPublication $publication)
    {
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
     * Descargar Excel
     */
    public function downloadExcel(ResultPublication $publication)
    {
        if (!$publication->excel_path) {
            abort(404, 'Excel no disponible');
        }

        $filePath = storage_path('app/public/' . $publication->excel_path);

        if (!file_exists($filePath)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->download($filePath);
    }

    /**
     * Generar nuevo Excel
     */
    public function generateExcel(ResultPublication $publication)
    {
        // TODO: Agregar autorización

        try {
            // Obtener aplicaciones según la fase
            $applications = Application::whereHas('vacancy.jobProfile.jobPosting',
                    fn($q) => $q->where('id', $publication->job_posting_id)
                )
                ->when($publication->phase === PublicationPhaseEnum::PHASE_04, fn($q) =>
                    $q->whereNotNull('eligibility_checked_at')
                )
                ->when($publication->phase === PublicationPhaseEnum::PHASE_07, fn($q) =>
                    $q->where('is_eligible', true)->whereNotNull('curriculum_score')
                )
                ->when($publication->phase === PublicationPhaseEnum::PHASE_09, fn($q) =>
                    $q->where('is_eligible', true)->whereNotNull('final_score')
                )
                ->with(['vacancy.jobProfile', 'applicant'])
                ->get()
                ->toArray();

            $export = $this->exportService->exportToExcel(
                $publication,
                $applications,
                $publication->phase->value
            );

            return redirect()
                ->back()
                ->with('success', 'Excel generado exitosamente');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al generar Excel: ' . $e->getMessage());
        }
    }
}
