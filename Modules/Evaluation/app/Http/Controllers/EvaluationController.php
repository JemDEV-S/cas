<?php

namespace Modules\Evaluation\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Evaluation\Services\EvaluationService;
use Modules\Evaluation\Entities\Evaluation;
use Modules\Evaluation\Http\Requests\{
    StoreEvaluationRequest,
    UpdateEvaluationRequest,
    SaveEvaluationDetailRequest,
    SubmitEvaluationRequest,
    ModifySubmittedEvaluationRequest
};
use Modules\Evaluation\Http\Resources\EvaluationResource;
use Modules\Evaluation\Enums\EvaluationStatusEnum;


class EvaluationController extends Controller
{
    use AuthorizesRequests;

    protected EvaluationService $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * Display a listing of evaluations (WEB).
     * GET /evaluations
     */
    public function index(Request $request)
    {
        // Si es una petición AJAX/API, devolver JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return $this->indexApi($request);
        }

        // Si es petición web, devolver vista
        $evaluatorId = auth()->id();

        $filters = [
            'status' => $request->input('status'),
            'phase_id' => $request->input('phase_id'),
            'requesting_unit_id' => $request->input('requesting_unit_id'),
            'pending_only' => $request->boolean('pending_only'),
            'completed_only' => $request->boolean('completed_only'),
            'per_page' => $request->input('per_page', 15),
        ];

        // Obtener asignaciones del evaluador (no evaluaciones directamente)
        $assignments = $this->evaluationService->getEvaluatorAssignments($evaluatorId, $filters);

        // Obtener unidades orgánicas para el filtro
        $organizationalUnits = \Modules\Organization\Entities\OrganizationalUnit::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('evaluation::index', [
            'assignments' => $assignments,
            'filters' => $filters,
            'organizationalUnits' => $organizationalUnits,
        ]);
    }

    /**
     * Display a listing of evaluations (API).
     * GET /api/evaluations
     */
    public function indexApi(Request $request): JsonResponse
    {
        $evaluatorId = $request->input('evaluator_id', auth()->id());

        $filters = [
            'status' => $request->input('status'),
            'phase_id' => $request->input('phase_id'),
            'requesting_unit_id' => $request->input('requesting_unit_id'),
            'pending_only' => $request->boolean('pending_only'),
            'completed_only' => $request->boolean('completed_only'),
            'per_page' => $request->input('per_page', 15),
        ];

        $assignments = $this->evaluationService->getEvaluatorAssignments($evaluatorId, $filters);

        return response()->json([
            'success' => true,
            'data' => $assignments->items(),
            'meta' => [
                'current_page' => $assignments->currentPage(),
                'total' => $assignments->total(),
                'per_page' => $assignments->perPage(),
            ],
        ]);
    }

    /**
     * Create and start a new evaluation from an assignment (WEB).
     * GET /evaluations/create?assignment_id={id}
     */
    public function create(Request $request)
    {
        try {
            // Obtener el assignment_id
            $assignmentId = $request->input('assignment_id');

            if (!$assignmentId) {
                return redirect()->route('evaluation.index')
                    ->with('error', 'No se especificó una asignación válida');
            }

            // Buscar la asignación
            $assignment = \Modules\Evaluation\Entities\EvaluatorAssignment::findOrFail($assignmentId);

            // Verificar que el usuario autenticado sea el evaluador asignado
            if ($assignment->user_id != auth()->id()) {
                return redirect()->route('evaluation.index')
                    ->with('error', 'No tienes permiso para iniciar esta evaluación');
            }

            // Verificar que no exista ya una evaluación para esta asignación
            if ($assignment->evaluation) {
                return redirect()->route('evaluation.evaluate', $assignment->evaluation->id)
                    ->with('info', 'Esta evaluación ya fue iniciada');
            }

            // Crear la evaluación
            $evaluation = $this->evaluationService->createEvaluation($assignment);

            // Redirigir al formulario de evaluación
            return redirect()->route('evaluation.evaluate', $evaluation->id)
                ->with('success', 'Evaluación iniciada exitosamente');

        } catch (\Exception $e) {
            \Log::error('Error al crear evaluación: ' . $e->getMessage());

            return redirect()->route('evaluation.index')
                ->with('error', 'Error al iniciar la evaluación: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created evaluation from an assignment (API).
     * POST /api/evaluations
     */
    public function store(StoreEvaluationRequest $request)
    {
        try {
            // Buscar la asignación
            $assignment = \Modules\Evaluation\Entities\EvaluatorAssignment::findOrFail(
                $request->input('evaluator_assignment_id')
            );

            // Verificar que no exista ya una evaluación para esta asignación
            if ($assignment->evaluation) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe una evaluación para esta asignación',
                    ], 422);
                }

                return redirect()->route('evaluation.evaluate', $assignment->evaluation->id)
                    ->with('info', 'Esta evaluación ya fue iniciada');
            }

            $evaluation = $this->evaluationService->createEvaluation($assignment);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Evaluación creada exitosamente',
                    'data' => new EvaluationResource($evaluation),
                ], 201);
            }

            return redirect()->route('evaluation.evaluate', $evaluation->id)
                ->with('success', 'Evaluación creada exitosamente');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la evaluación',
                    'error' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Error al crear la evaluación: ' . $e->getMessage());
        }
    }

    /**
     * Show evaluation form (WEB).
     * GET /evaluations/{id}/evaluate
     */
    public function evaluate(int $id)
    {
        try {
            $evaluation = Evaluation::with([
                'details.criterion',
                'evaluatorAssignment.application.jobProfile.positionCode',
                'evaluatorAssignment.application.jobProfile.jobPosting',
                'evaluatorAssignment.application.documents',
                'evaluatorAssignment.application.trainings',
                'evaluatorAssignment.application.academics',
                'evaluatorAssignment.application.experiences',
                'evaluatorAssignment.phase',
                'phase'
            ])->findOrFail($id);

            // Verificar que el usuario autenticado sea el evaluador
            if ($evaluation->evaluatorAssignment && $evaluation->evaluatorAssignment->user_id != auth()->id()) {
                return redirect()->route('evaluation.index')
                    ->with('error', 'No tienes permiso para evaluar esta postulación');
            }

            // Obtener el position_code_id y código desde la postulación
            $positionCodeId = null;
            $positionCode = null;
            if ($evaluation->evaluatorAssignment &&
                $evaluation->evaluatorAssignment->application &&
                $evaluation->evaluatorAssignment->application->jobProfile) {

                $jobProfile = $evaluation->evaluatorAssignment->application->jobProfile;

                // Obtener el ID y código del puesto
                if ($jobProfile->position_code_id) {
                    $positionCodeId = $jobProfile->position_code_id;
                }
                if ($jobProfile->positionCode) {
                    $positionCode = $jobProfile->positionCode->code;
                }
            }

            // Obtener el CV del postulante
            $cvDocument = null;
            if ($evaluation->evaluatorAssignment && $evaluation->evaluatorAssignment->application) {
                $cvDocument = $evaluation->evaluatorAssignment->application->documents()
                    ->where('document_type', 'DOC_CV')
                    ->first();
            }

            // Obtener criterios de evaluación para la fase y el puesto específico
            $criteriaQuery = \Modules\Evaluation\Entities\EvaluationCriterion::active()
                ->byPhase($evaluation->phase_id);

            // Filtrar por position_code_id si existe
            if ($positionCodeId) {
                $criteriaQuery->byPositionCode($positionCodeId);
            }

            $criteria = $criteriaQuery->ordered()->get();

            // Calcular puntaje máximo total
            $maxTotalScore = $criteria->sum('max_score');

            // Crear array con detalles indexados por criterion_id
            $details = [];
            foreach ($evaluation->details as $detail) {
                $details[$detail->criterion_id] = $detail;
            }

            // Obtener application y jobProfile
            $application = $evaluation->evaluatorAssignment->application ?? null;
            $jobProfile = $application ? $application->jobProfile : null;

            // Detectar si es evaluación de entrevista (PHASE_08)
            $isInterviewEvaluation = $evaluation->phase &&
                                    $evaluation->phase->code === 'PHASE_08_INTERVIEW';

            // Seleccionar la vista apropiada
            $viewName = $isInterviewEvaluation
                ? 'evaluation::evaluations.evaluate-interview'
                : 'evaluation::evaluations.evaluate';

            return view($viewName, [
                'evaluation' => $evaluation,
                'criteria' => $criteria,
                'positionCode' => $positionCode,
                'maxTotalScore' => $maxTotalScore,
                'cvDocument' => $cvDocument,
                'details' => $details,
                'application' => $application,
                'jobProfile' => $jobProfile,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al cargar formulario de evaluación: ' . $e->getMessage());

            return redirect()->route('evaluation.index')
                ->with('error', 'Error al cargar la evaluación');
        }
    }

    /**
     * View CV document in evaluation.
     * GET /evaluations/{id}/view-cv
     */
    public function viewCV(int $id)
    {
        try {
            $evaluation = Evaluation::with([
                'evaluatorAssignment.application.documents'
            ])->findOrFail($id);

            // Verificar permisos
            if ($evaluation->evaluatorAssignment && $evaluation->evaluatorAssignment->user_id != auth()->id()) {
                abort(403, 'No tienes permiso para ver este documento');
            }

            // Buscar el CV
            if (!$evaluation->evaluatorAssignment || !$evaluation->evaluatorAssignment->application) {
                abort(404, 'No se encontró la postulación');
            }

            $cvDocument = $evaluation->evaluatorAssignment->application->documents()
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

        } catch (\Exception $e) {
            \Log::error('Error al ver CV: ' . $e->getMessage());
            abort(404, 'Documento no disponible');
        }
    }

    /**
     * Display the specified evaluation (API/JSON).
     * GET /api/evaluations/{id}
     */
    public function show($id)
    {
        try {
            // Intentar encontrar por ID o UUID
            $evaluation = Evaluation::where('id', $id)
                ->orWhere('uuid', $id)
                ->with([
                    'details.criterion',
                    'evaluator',
                    'application',
                    'phase',
                    'jobPosting',
                    'evaluatorAssignment'
                ])
                ->firstOrFail();

            // Verificar autorización
            //$this->authorize('view', $evaluation);

            // Si es petición JSON
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => new EvaluationResource($evaluation),
                ]);
            }

            // Si es petición web, mostrar vista de detalle
            return view('evaluation::evaluations.show', [
                'evaluation' => $evaluation,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Evaluación no encontrada', [
                'id' => $id,
                'user_id' => auth()->id(),
            ]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Evaluación no encontrada',
                    'error' => "No se encontró evaluación con ID: {$id}",
                ], 404);
            }

            return redirect()->route('evaluation.index')
                ->with('error', "Evaluación no encontrada (ID: {$id})");

        } catch (\Exception $e) {
            \Log::error('Error al obtener evaluación', [
                'id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al obtener la evaluación',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return redirect()->route('evaluation.index')
                ->with('error', 'Error al obtener la evaluación: ' . $e->getMessage());
        }
    }
    /**
     * Update the specified evaluation (draft mode).
     * PUT /api/evaluations/{id}
     */
    public function update(UpdateEvaluationRequest $request, int $id): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($id);

            // Verificar autorización
            $this->authorize('update', $evaluation);

            $updatedEvaluation = $this->evaluationService->updateEvaluation(
                $evaluation,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Evaluación actualizada exitosamente',
                'data' => new EvaluationResource($updatedEvaluation),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la evaluación',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Save or update evaluation detail (score for a criterion).
     * POST /api/evaluations/{id}/details
     */
    public function saveDetail(SaveEvaluationDetailRequest $request, int $id): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($id);

            // Verificar autorización
            $this->authorize('update', $evaluation);

            $detail = $this->evaluationService->saveEvaluationDetail(
                $evaluation,
                $request->validated()
            );

            // Recargar evaluación con totales actualizados
            $evaluation->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Calificación guardada exitosamente',
                'data' => [
                    'detail' => $detail,
                    'evaluation' => new EvaluationResource($evaluation),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la calificación',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Submit the evaluation (finalize).
     * POST /api/evaluations/{id}/submit
     */
    public function submit(SubmitEvaluationRequest $request, int $id): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($id);

            // Verificar autorización
            $this->authorize('submit', $evaluation);

            // Si viene información de descalificación, guardarla
            if ($request->has('disqualified') && $request->input('disqualified') === true) {
                $metadata = $evaluation->metadata ?? [];
                $metadata['disqualified'] = true;
                $metadata['disqualification_type'] = $request->input('disqualification_type') ?? null;

                $evaluation->update([
                    'metadata' => $metadata,
                    'general_comments' => $request->input('disqualification_reason'),
                    'total_score' => 0,
                ]);
            } else {
                // Actualizar comentarios generales si se proporcionan
                if ($request->has('general_comments')) {
                    $evaluation->update([
                        'general_comments' => $request->input('general_comments'),
                    ]);
                }
            }

            $submittedEvaluation = $this->evaluationService->submitEvaluation($evaluation);

            return response()->json([
                'success' => true,
                'message' => 'Evaluación enviada exitosamente. Ya no podrá ser modificada.',
                'data' => new EvaluationResource($submittedEvaluation),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la evaluación',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Modify a submitted evaluation (admin only).
     * POST /api/evaluations/{id}/modify
     */
    public function modifySubmitted(ModifySubmittedEvaluationRequest $request, int $id): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($id);

            // Verificar autorización (solo admin)
            $this->authorize('modifySubmitted', $evaluation);

            $modifiedEvaluation = $this->evaluationService->modifySubmittedEvaluation(
                $evaluation,
                $request->only(['details', 'general_comments']),
                $request->input('modification_reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'Evaluación modificada exitosamente',
                'data' => new EvaluationResource($modifiedEvaluation),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al modificar la evaluación',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified evaluation.
     * DELETE /evaluations/{id} (WEB)
     * DELETE /api/evaluations/{id} (API)
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $evaluation = Evaluation::findOrFail($id);

            // Verificar que el usuario sea el dueño de la evaluación
            if ($evaluation->evaluator_id != auth()->id()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permiso para eliminar esta evaluación',
                    ], 403);
                }

                return redirect()->route('evaluation.my-evaluations')
                    ->with('error', 'No tienes permiso para eliminar esta evaluación');
            }

            // Verificar que la evaluación esté completada
            if (!in_array($evaluation->status, [EvaluationStatusEnum::SUBMITTED, EvaluationStatusEnum::MODIFIED])) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Solo puedes eliminar evaluaciones completadas',
                    ], 422);
                }

                return redirect()->route('evaluation.my-evaluations')
                    ->with('error', 'Solo puedes eliminar evaluaciones completadas');
            }

            $this->evaluationService->deleteEvaluation($evaluation);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Evaluación eliminada exitosamente. Puedes volver a evaluar esta postulación.',
                ]);
            }

            return redirect()->route('evaluation.my-evaluations')
                ->with('success', 'Evaluación eliminada exitosamente. Puedes volver a evaluar esta postulación.');

        } catch (\Exception $e) {
            \Log::error('Error al eliminar evaluación: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la evaluación',
                    'error' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('evaluation.my-evaluations')
                ->with('error', 'Error al eliminar la evaluación: ' . $e->getMessage());
        }
    }

    /**
     * Get evaluation history.
     * GET /api/evaluations/{id}/history
     */
    public function history(int $id): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($id);

            // Verificar autorización
            $this->authorize('view', $evaluation);

            $history = $evaluation->history()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $history,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get evaluation statistics.
     * GET /api/evaluations/stats
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $filters = [
                'evaluator_id' => $request->input('evaluator_id', auth()->id()),
                'phase_id' => $request->input('phase_id'),
                'job_posting_id' => $request->input('job_posting_id'),
            ];

            $stats = $this->evaluationService->getEvaluationStats($filters);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage(),
            ], 422);
        }
    }


    /**
     * Get my evaluations (authenticated evaluator).
     * Solo muestra evaluaciones completadas (SUBMITTED, MODIFIED).
     * GET /evaluations/my-evaluations (WEB)
     * GET /api/evaluations/my-evaluations (API)
     */
    public function myEvaluations(Request $request)
    {
        try {
            $userId = auth()->id();

            // Obtener evaluaciones completadas del usuario
            $query = Evaluation::with([
                'evaluatorAssignment.application.jobProfile.positionCode',
                'evaluatorAssignment.application.jobProfile.requestingUnit',
                'evaluatorAssignment.application.jobProfile.jobPosting',
                'evaluatorAssignment.application',
                'evaluatorAssignment.phase',
                'phase'
            ])
            ->where('evaluator_id', $userId)
            ->whereIn('status', [EvaluationStatusEnum::SUBMITTED, EvaluationStatusEnum::MODIFIED]);

            // Filtro de búsqueda
            if ($request->has('search') && $request->input('search') != '') {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    // Buscar por nombre del postulante
                    $q->whereHas('evaluatorAssignment.application', function($subQuery) use ($search) {
                        $subQuery->where('full_name', 'like', "%{$search}%")
                                 ->orWhere('dni', 'like', "%{$search}%");
                    })
                    // Buscar por unidad orgánica
                    ->orWhereHas('evaluatorAssignment.application.jobProfile.requestingUnit', function($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('code', 'like', "%{$search}%");
                    });
                });
            }

            // Filtros adicionales
            if ($request->has('phase_id') && $request->input('phase_id') != '') {
                $query->where('phase_id', $request->input('phase_id'));
            }

            if ($request->has('requesting_unit_id') && $request->input('requesting_unit_id') != '') {
                $query->whereHas('evaluatorAssignment.application.jobProfile', function($subQuery) use ($request) {
                    $subQuery->where('requesting_unit_id', $request->input('requesting_unit_id'));
                });
            }

            if ($request->has('job_posting_id') && $request->input('job_posting_id') != '') {
                $query->where('job_posting_id', $request->input('job_posting_id'));
            }

            // Filtro por estado específico
            if ($request->has('status') && $request->input('status') != '') {
                $query->where('status', $request->input('status'));
            }

            // Filtro por rango de puntaje
            if ($request->has('min_score') && $request->input('min_score') !== '') {
                $minScore = floatval($request->input('min_score'));
                $query->where('percentage', '>=', $minScore);
            }

            if ($request->has('max_score') && $request->input('max_score') !== '') {
                $maxScore = floatval($request->input('max_score'));
                $query->where('percentage', '<=', $maxScore);
            }

            // Filtro por comentarios
            if ($request->has('has_comments') && $request->input('has_comments') !== '') {
                if ($request->input('has_comments') == '1') {
                    // Con comentarios
                    $query->whereNotNull('general_comments')
                          ->where('general_comments', '!=', '');
                } else {
                    // Sin comentarios
                    $query->where(function($q) {
                        $q->whereNull('general_comments')
                          ->orWhere('general_comments', '=', '');
                    });
                }
            }

            // Filtro por evaluaciones problemáticas (< 35 puntos sin comentarios)
            if ($request->has('problematic') && $request->input('problematic') == '1') {
                $query->where('total_score', '<', 35)
                      ->where(function($q) {
                          $q->whereNull('general_comments')
                            ->orWhere('general_comments', '=', '');
                      });
            }

            // Filtro por fecha de envío
            if ($request->has('date_from') && $request->input('date_from') != '') {
                $query->whereDate('submitted_at', '>=', $request->input('date_from'));
            }

            if ($request->has('date_to') && $request->input('date_to') != '') {
                $query->whereDate('submitted_at', '<=', $request->input('date_to'));
            }

            $evaluations = $query->orderBy('submitted_at', 'desc')
                ->paginate($request->input('per_page', 15))
                ->appends($request->query());

            // Obtener unidades orgánicas para filtros
            $organizationalUnits = \Modules\Organization\Entities\OrganizationalUnit::where('is_active', true)
                ->orderBy('name')
                ->get();

            // Obtener fases para filtros
            $phases = \Modules\JobPosting\Entities\ProcessPhase::where('is_active', true)
                ->orderBy('order')
                ->get();

            // Stats
            $stats = [
                'total' => Evaluation::where('evaluator_id', $userId)
                    ->whereIn('status', [EvaluationStatusEnum::SUBMITTED, EvaluationStatusEnum::MODIFIED])
                    ->count(),
                'submitted' => Evaluation::where('evaluator_id', $userId)
                    ->where('status', EvaluationStatusEnum::SUBMITTED)
                    ->count(),
                'modified' => Evaluation::where('evaluator_id', $userId)
                    ->where('status', EvaluationStatusEnum::MODIFIED)
                    ->count(),
                'average_score' => Evaluation::where('evaluator_id', $userId)
                    ->whereIn('status', [EvaluationStatusEnum::SUBMITTED, EvaluationStatusEnum::MODIFIED])
                    ->avg('percentage') ?? 0,
                'problematic' => Evaluation::where('evaluator_id', $userId)
                    ->whereIn('status', [EvaluationStatusEnum::SUBMITTED, EvaluationStatusEnum::MODIFIED])
                    ->where('total_score', '<', 35)
                    ->where(function($q) {
                        $q->whereNull('general_comments')
                          ->orWhere('general_comments', '=', '');
                    })
                    ->count(),
            ];

            // Si es petición API
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $evaluations->items(),
                    'stats' => $stats,
                    'meta' => [
                        'current_page' => $evaluations->currentPage(),
                        'total' => $evaluations->total(),
                        'per_page' => $evaluations->perPage(),
                    ],
                ]);
            }

            // Retornar vista WEB
            return view('evaluation::my-evaluations', compact(
                'evaluations',
                'stats',
                'organizationalUnits',
                'phases'
            ));

        } catch (\Exception $e) {
            \Log::error('Error in myEvaluations: ' . $e->getMessage());

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al obtener tus evaluaciones',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return view('evaluation::my-evaluations', [
                'evaluations' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'stats' => ['total' => 0, 'submitted' => 0, 'modified' => 0, 'average_score' => 0],
                'organizationalUnits' => collect([]),
                'phases' => collect([]),
            ])->with('error', 'Error al cargar tus evaluaciones');
        }
    }
}
