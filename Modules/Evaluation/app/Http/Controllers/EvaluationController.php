<?php

namespace Modules\Evaluation\Http\Controllers;

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
use Modules\Evaluation\Resources\EvaluationResource;

class EvaluationController extends Controller
{
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
            'pending_only' => $request->boolean('pending_only'),
            'completed_only' => $request->boolean('completed_only'),
            'per_page' => $request->input('per_page', 15),
        ];

        $evaluations = $this->evaluationService->getEvaluatorEvaluations($evaluatorId, $filters);

        return view('evaluation::index', [
            'evaluations' => $evaluations,
            'filters' => $filters,
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
            'pending_only' => $request->boolean('pending_only'),
            'completed_only' => $request->boolean('completed_only'),
            'per_page' => $request->input('per_page', 15),
        ];

        $evaluations = $this->evaluationService->getEvaluatorEvaluations($evaluatorId, $filters);

        return response()->json([
            'success' => true,
            'data' => EvaluationResource::collection($evaluations),
            'meta' => [
                'current_page' => $evaluations->currentPage(),
                'total' => $evaluations->total(),
                'per_page' => $evaluations->perPage(),
            ],
        ]);
    }

    /**
     * Store a newly created evaluation.
     * POST /api/evaluations
     */
    public function store(StoreEvaluationRequest $request): JsonResponse
    {
        try {
            $evaluation = $this->evaluationService->createEvaluation($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Evaluación creada exitosamente',
                'data' => new EvaluationResource($evaluation),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la evaluación',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified evaluation.
     * GET /api/evaluations/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $evaluation = Evaluation::with([
                'details.criterion',
                'evaluator',
                'application',
                'phase',
                'jobPosting'
            ])->findOrFail($id);

            // Verificar autorización
            $this->authorize('view', $evaluation);

            return response()->json([
                'success' => true,
                'data' => new EvaluationResource($evaluation),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la evaluación',
                'error' => $e->getMessage(),
            ], 404);
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

            // Actualizar comentarios generales si se proporcionan
            if ($request->has('general_comments')) {
                $evaluation->update([
                    'general_comments' => $request->input('general_comments'),
                ]);
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
     * DELETE /api/evaluations/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($id);
            
            // Verificar autorización
            $this->authorize('delete', $evaluation);

            $this->evaluationService->deleteEvaluation($evaluation);

            return response()->json([
                'success' => true,
                'message' => 'Evaluación eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la evaluación',
                'error' => $e->getMessage(),
            ], 422);
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
     * GET /api/evaluations/my-evaluations
     */
    public function myEvaluations(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->input('status'),
            'phase_id' => $request->input('phase_id'),
            'pending_only' => $request->boolean('pending_only'),
            'completed_only' => $request->boolean('completed_only'),
            'per_page' => $request->input('per_page', 15),
        ];

        $evaluations = $this->evaluationService->getEvaluatorEvaluations(
            auth()->id(),
            $filters
        );

        return response()->json([
            'success' => true,
            'data' => EvaluationResource::collection($evaluations),
            'meta' => [
                'current_page' => $evaluations->currentPage(),
                'total' => $evaluations->total(),
                'per_page' => $evaluations->perPage(),
            ],
        ]);
    }
}