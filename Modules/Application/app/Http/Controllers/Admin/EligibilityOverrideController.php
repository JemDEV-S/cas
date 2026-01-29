<?php

namespace Modules\Application\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Application\Entities\Application;
use Modules\Application\Services\EligibilityOverrideService;
use Modules\Application\Http\Requests\ApproveOverrideRequest;
use Modules\Application\Http\Requests\RejectOverrideRequest;
use Modules\Application\Http\Resources\EligibilityOverrideResource;
use Modules\JobPosting\Entities\JobPosting;
use Illuminate\Support\Facades\DB;

class EligibilityOverrideController extends Controller
{
    public function __construct(
        private EligibilityOverrideService $service
    ) {}

    /**
     * Mostrar lista de postulaciones para reevaluar
     */
    public function index(Request $request, string $postingId)
    {
        $this->authorize('eligibility.override');

        $posting = JobPosting::with(['jobProfiles'])->findOrFail($postingId);

        $jobProfileId = $request->get('job_profile_id');
        $phaseId = $request->get('phase_id');

        // Obtener postulaciones pendientes de revisión
        $pendingApplications = $this->service->getApplicationsForReview($postingId, $jobProfileId, $phaseId);

        // Obtener postulaciones ya resueltas
        $resolvedApplications = $this->service->getResolvedApplications($postingId, $jobProfileId, $phaseId);

        // Estadísticas
        $stats = $this->service->getStatistics($postingId, $phaseId, $jobProfileId);

        // Obtener fases activas para el filtro
        $phases = \Modules\JobPosting\Entities\ProcessPhase::where('is_active', true)
            ->where('requires_evaluation', true)
            ->orderBy('phase_number')
            ->get();

        return view('application::admin.eligibility-override.index', compact(
            'posting',
            'pendingApplications',
            'resolvedApplications',
            'stats',
            'jobProfileId',
            'phaseId',
            'phases'
        ));
    }

    /**
     * Mostrar detalle de postulación para reevaluar
     */
    public function show(string $applicationId)
    {
        $this->authorize('eligibility.override');

        $application = Application::findOrFail($applicationId);
        $application = $this->service->getApplicationDetail($application);

        $canBeReviewed = $this->service->canBeReviewed($application);

        return view('application::admin.eligibility-override.show', compact(
            'application',
            'canBeReviewed'
        ));
    }

    /**
     * Aprobar reevaluación (cambiar a APTO)
     */
    public function approve(ApproveOverrideRequest $request, string $applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if (!$this->service->canBeReviewed($application)) {
            return redirect()
                ->back()
                ->with('error', 'Esta postulación no puede ser reevaluada');
        }

        try {
            $override = $this->service->approve(
                $application,
                $request->resolution_summary,
                $request->resolution_detail,
                auth()->id(),
                $request->resolution_type ?? 'CLAIM'
            );

            return redirect()
                ->route('admin.eligibility-override.show', $application->id)
                ->with('success', 'Reclamo aprobado. El postulante ahora está en estado APTO.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Rechazar reevaluación (mantener NO_APTO)
     */
    public function reject(RejectOverrideRequest $request, string $applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if (!$this->service->canBeReviewed($application)) {
            return redirect()
                ->back()
                ->with('error', 'Esta postulación no puede ser reevaluada');
        }

        try {
            $override = $this->service->reject(
                $application,
                $request->resolution_summary,
                $request->resolution_detail,
                auth()->id(),
                $request->resolution_type ?? 'CLAIM'
            );

            return redirect()
                ->route('admin.eligibility-override.show', $application->id)
                ->with('success', 'Reclamo rechazado. El postulante mantiene estado NO APTO.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Generar PDF de resoluciones por convocatoria y fase
     */
    public function generatePdf(Request $request, string $postingId)
    {
        $this->authorize('eligibility.override');

        $posting = JobPosting::with(['jobProfiles.organizationalUnit'])->findOrFail($postingId);

        // Obtener filtros
        $phaseId = $request->get('phase_id');
        $jobProfileId = $request->get('job_profile_id');

        // Obtener postulaciones con override resuelto filtradas
        $resolvedApplications = $this->service->getResolvedApplications($postingId, $jobProfileId, $phaseId);

        if ($resolvedApplications->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'No hay resoluciones de reevaluación para los filtros seleccionados');
        }

        // Estadísticas filtradas
        $stats = $this->service->getStatistics($postingId, $phaseId, $jobProfileId);

        // Obtener información de la fase si existe
        $phase = null;
        $phaseName = 'Todas las Fases';
        if ($phaseId) {
            $phase = \Modules\JobPosting\Entities\ProcessPhase::find($phaseId);
            $phaseName = $phase ? $phase->name : 'Fase Desconocida';
        }

        // Agrupar por unidad organizacional y perfil
        $units = [];
        foreach ($resolvedApplications as $application) {
            $profile = $application->jobProfile;
            $unitName = $profile->organizationalUnit->name ?? 'Sin Unidad Asignada';
            $unitId = $profile->organizational_unit_id ?? 'sin_unidad';

            if (!isset($units[$unitId])) {
                $units[$unitId] = [
                    'name' => $unitName,
                    'profiles' => [],
                    'stats' => ['total' => 0, 'approved' => 0, 'rejected' => 0],
                ];
            }

            $profileId = $profile->id;
            if (!isset($units[$unitId]['profiles'][$profileId])) {
                $units[$unitId]['profiles'][$profileId] = [
                    'code' => $profile->code,
                    'title' => $profile->profile_name ?? $profile->title,
                    'position_code' => $profile->position_code ?? 'N/A',
                    'vacancies' => $profile->total_vacancies ?? 1,
                    'applications' => [],
                    'stats' => ['total' => 0, 'approved' => 0, 'rejected' => 0],
                ];
            }

            $units[$unitId]['profiles'][$profileId]['applications'][] = $application;
            $units[$unitId]['profiles'][$profileId]['stats']['total']++;
            $units[$unitId]['stats']['total']++;

            if ($application->eligibilityOverride->decision->value === 'APPROVED') {
                $units[$unitId]['profiles'][$profileId]['stats']['approved']++;
                $units[$unitId]['stats']['approved']++;
            } else {
                $units[$unitId]['profiles'][$profileId]['stats']['rejected']++;
                $units[$unitId]['stats']['rejected']++;
            }
        }

        // Convertir a array indexado
        $units = array_values($units);
        foreach ($units as &$unit) {
            $unit['profiles'] = array_values($unit['profiles']);
        }

        $pdf = Pdf::loadView('document::templates.result_eligibility_override', [
            'posting' => $posting,
            'units' => $units,
            'stats' => $stats,
            'title' => 'RESULTADO DE REEVALUACION DE ELEGIBILIDAD',
            'subtitle' => 'Resolucion de Reclamos - Proceso CAS',
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i'),
            'phase' => $phase ? $phase->name : 'Reevaluacion de Elegibilidad (Todas las Fases)',
            'phase_name' => $phaseName,
        ]);

        $pdf->setPaper('A4', 'landscape');

        // Nombre de archivo con fase si aplica
        $filename = "resultado-reevaluacion-{$posting->code}";
        if ($phaseId && $phase) {
            $filename .= "-fase-" . \Str::slug($phase->code);
        }
        $filename .= ".pdf";

        return $pdf->download($filename);
    }

    /**
     * API: Listar postulaciones para reevaluar
     */
    public function apiIndex(Request $request)
    {
        $this->authorize('eligibility.override');

        $applications = $this->service->getApplicationsForReview(
            $request->job_posting_id,
            $request->job_profile_id
        );

        return response()->json([
            'data' => $applications,
            'meta' => [
                'total' => $applications->count(),
            ]
        ]);
    }

    /**
     * API: Aprobar reevaluación
     */
    public function apiApprove(ApproveOverrideRequest $request, Application $application)
    {
        if (!$this->service->canBeReviewed($application)) {
            return response()->json([
                'error' => 'Esta postulación no puede ser reevaluada'
            ], 422);
        }

        try {
            $override = $this->service->approve(
                $application,
                $request->resolution_summary,
                $request->resolution_detail,
                auth()->id(),
                $request->resolution_type ?? 'CLAIM'
            );

            return new EligibilityOverrideResource($override->load('resolver', 'application'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * API: Rechazar reevaluación
     */
    public function apiReject(RejectOverrideRequest $request, Application $application)
    {
        if (!$this->service->canBeReviewed($application)) {
            return response()->json([
                'error' => 'Esta postulación no puede ser reevaluada'
            ], 422);
        }

        try {
            $override = $this->service->reject(
                $application,
                $request->resolution_summary,
                $request->resolution_detail,
                auth()->id(),
                $request->resolution_type ?? 'CLAIM'
            );

            return new EligibilityOverrideResource($override->load('resolver', 'application'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Ver ficha de postulación PDF en el navegador
     */
    public function viewApplicationSheet(string $applicationId)
    {
        $this->authorize('eligibility.override');

        $application = Application::findOrFail($applicationId);

        // Buscar el documento de ficha de postulación
        $document = $application->generatedDocuments()
            ->whereHas('template', fn($q) => $q->where('code', 'TPL_APPLICATION_SHEET'))
            ->first();

        if (!$document) {
            return redirect()
                ->back()
                ->with('error', 'No se encontró la ficha de postulación para este postulante.');
        }

        // Verificar que el PDF existe
        if (!$document->pdf_path || !Storage::disk('private')->exists($document->pdf_path)) {
            return redirect()
                ->back()
                ->with('error', 'El archivo PDF no existe.');
        }

        // Retornar el PDF para visualización en el navegador (inline)
        return response(Storage::disk('private')->get($document->pdf_path), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Ficha_Postulacion_' . $application->code . '.pdf"');
    }

    /**
     * Revisar CV y modificar calificaciones (simple y directo)
     * GET /admin/eligibility-override/{application}/review-cv
     */
    public function reviewCV(string $applicationId)
    {
        $this->authorize('eligibility.override');

        $application = Application::with([
            'applicant',
            'jobProfile.positionCode',
            'jobProfile.jobPosting',
            'documents',
            'eligibilityOverride',
        ])->findOrFail($applicationId);

        // Buscar fase de CV
        $cvPhase = \Modules\JobPosting\Entities\ProcessPhase::where('code', 'PHASE_06_CV_EVALUATION')
            ->orWhere(function($q) {
                $q->where('name', 'like', '%Evaluación Curricular%')
                  ->orWhere('name', 'like', '%Evaluación de Currículo%');
            })
            ->first();

        if (!$cvPhase) {
            return redirect()
                ->back()
                ->with('error', 'No se encontró la fase de Evaluación de CV en el sistema.');
        }

        // Buscar evaluación de CV
        $cvEvaluation = \Modules\Evaluation\Entities\Evaluation::where('application_id', $application->id)
            ->where('phase_id', $cvPhase->id)
            ->whereIn('status', ['SUBMITTED', 'MODIFIED'])
            ->with(['details.criterion', 'evaluator', 'phase'])
            ->first();

        if (!$cvEvaluation) {
            return redirect()
                ->back()
                ->with('error', 'No se encontró una evaluación de CV completada para este postulante.');
        }

        // Obtener CV
        $cvDocument = $application->documents()
            ->where('document_type', 'DOC_CV')
            ->first();

        // Obtener el position_code del perfil
        $jobProfile = $application->jobProfile;
        $positionCodeId = $jobProfile->position_code_id;
        $positionCode = $jobProfile->positionCode ? $jobProfile->positionCode->code : null;

        // Obtener criterios filtrados por phase_id y position_code_id
        $criteria = \Modules\Evaluation\Entities\EvaluationCriterion::active()
            ->byPhase($cvPhase->id)
            ->when($positionCodeId, function ($query) use ($positionCodeId) {
                $query->where('position_code_id', $positionCodeId);
            })
            ->ordered()
            ->get();

        // Crear array con detalles indexados por criterion_id
        $details = [];
        foreach ($cvEvaluation->details as $detail) {
            $details[$detail->criterion_id] = $detail;
        }

        return view('application::admin.eligibility-override.review-cv', compact(
            'application',
            'jobProfile',
            'positionCode',
            'cvDocument',
            'cvEvaluation',
            'criteria',
            'details'
        ));
    }

    /**
     * Ver CV del postulante en iframe
     * GET /admin/eligibility-override/{application}/view-cv
     */
    public function viewCV(string $applicationId)
    {
        $this->authorize('eligibility.override');

        $application = Application::with('documents')->findOrFail($applicationId);

        $cvDocument = $application->documents()
            ->where('document_type', 'DOC_CV')
            ->first();

        if (!$cvDocument || !$cvDocument->fileExists()) {
            abort(404, 'CV no encontrado');
        }

        $filePath = storage_path('app/' . $cvDocument->file_path);

        return response()->file($filePath, [
            'Content-Type' => $cvDocument->mime_type,
            'Content-Disposition' => 'inline; filename="' . $cvDocument->file_name . '"'
        ]);
    }

    /**
     * Actualizar calificaciones de CV y resolver reclamo
     * POST /admin/eligibility-override/{application}/update-cv-review
     */
    public function updateCVReview(Request $request, string $applicationId)
    {
        $this->authorize('eligibility.override');

        $validated = $request->validate([
            'criteria' => 'required|array',
            'criteria.*.score' => 'required|numeric|min:0',
            'general_comments' => 'nullable|string',
            'decision' => 'required|in:APPROVED,REJECTED',
            'resolution_summary' => 'required|string|max:500',
            'resolution_detail' => 'required|string',
        ]);

        $application = Application::findOrFail($applicationId);

        // Buscar fase de CV
        $cvPhase = \Modules\JobPosting\Entities\ProcessPhase::where('code', 'PHASE_06_CV_EVALUATION')
            ->orWhere(function($q) {
                $q->where('name', 'like', '%Evaluación Curricular%')
                  ->orWhere('name', 'like', '%Evaluación de Currículo%');
            })
            ->first();

        if (!$cvPhase) {
            return redirect()
                ->back()
                ->with('error', 'No se encontró la fase de Evaluación de CV.');
        }

        // Buscar evaluación de CV
        $cvEvaluation = \Modules\Evaluation\Entities\Evaluation::where('application_id', $application->id)
            ->where('phase_id', $cvPhase->id)
            ->whereIn('status', ['SUBMITTED', 'MODIFIED'])
            ->first();

        if (!$cvEvaluation) {
            return redirect()
                ->back()
                ->with('error', 'No se encontró una evaluación de CV para este postulante.');
        }

        try {
            DB::transaction(function () use ($request, $application, $cvEvaluation, $validated, $cvPhase) {
                $totalScore = 0;

                // Actualizar detalles de evaluación
                foreach ($validated['criteria'] as $criterionId => $data) {
                    $score = floatval($data['score'] ?? 0);

                    // Actualizar o crear detalle
                    \Modules\Evaluation\Entities\EvaluationDetail::updateOrCreate(
                        [
                            'evaluation_id' => $cvEvaluation->id,
                            'criterion_id' => $criterionId,
                        ],
                        [
                            'score' => $score,
                            'comments' => null,
                            'evidence' => null,
                        ]
                    );

                    // Calcular puntaje ponderado
                    $criterion = \Modules\Evaluation\Entities\EvaluationCriterion::find($criterionId);
                    if ($criterion) {
                        $totalScore += $score * $criterion->weight;
                    }
                }

                // Actualizar evaluación
                $cvEvaluation->update([
                    'total_score' => $totalScore,
                    'general_comments' => $validated['general_comments'],
                    'status' => 'MODIFIED',
                    'modified_by' => auth()->id(),
                    'modified_at' => now(),
                    'modification_reason' => 'Reevaluación por reclamo de elegibilidad',
                ]);

                // Resolver reclamo según decisión
                if ($validated['decision'] === 'APPROVED') {
                    // Aprobar: cambiar a APTO
                    app(EligibilityOverrideService::class)->approve(
                        $application,
                        $validated['resolution_summary'],
                        $validated['resolution_detail'],
                        auth()->id(),
                        'CLAIM',
                        $cvPhase->id  // Pasar el phase_id afectado
                    );
                } else {
                    // Rechazar: mantener NO_APTO
                    app(EligibilityOverrideService::class)->reject(
                        $application,
                        $validated['resolution_summary'],
                        $validated['resolution_detail'],
                        auth()->id(),
                        'CLAIM',
                        $cvPhase->id  // Pasar el phase_id afectado
                    );
                }
            });

            return redirect()
                ->route('admin.eligibility-override.index', $application->jobProfile->job_posting_id)
                ->with('success', 'Revisión de CV completada y reclamo resuelto exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error al actualizar revisión de CV: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Error al procesar la revisión: ' . $e->getMessage());
        }
    }
}
