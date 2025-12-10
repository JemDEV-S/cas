<?php

namespace Modules\Evaluation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Evaluation\Services\EvaluatorAssignmentService;
use Modules\Evaluation\Entities\EvaluatorAssignment;
use Modules\Evaluation\Http\Requests\{
    AssignEvaluatorRequest,
    AutoAssignEvaluatorsRequest
};

class EvaluatorAssignmentController extends Controller
{
    protected EvaluatorAssignmentService $assignmentService;

    public function __construct(EvaluatorAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display a listing of assignments.
     * GET /api/evaluator-assignments
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EvaluatorAssignment::with(['evaluator', 'application', 'phase', 'jobPosting']);

            // Filtros
            if ($request->has('evaluator_id')) {
                $query->byEvaluator($request->input('evaluator_id'));
            }

            if ($request->has('phase_id')) {
                $query->byPhase($request->input('phase_id'));
            }

            if ($request->has('job_posting_id')) {
                $query->where('job_posting_id', $request->input('job_posting_id'));
            }

            if ($request->has('status')) {
                $query->byStatus($request->input('status'));
            }

            if ($request->boolean('pending_only')) {
                $query->pending();
            }

            if ($request->boolean('active_only')) {
                $query->active();
            }

            if ($request->boolean('overdue_only')) {
                $query->overdue();
            }

            // Ordenamiento
            $query->orderBy('deadline_at', 'asc')
                ->orderBy('created_at', 'desc');

            $assignments = $query->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $assignments->items(),
                'meta' => [
                    'current_page' => $assignments->currentPage(),
                    'total' => $assignments->total(),
                    'per_page' => $assignments->perPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las asignaciones',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Assign evaluator manually to an application.
     * POST /api/evaluator-assignments/assign
     */
    public function assign(AssignEvaluatorRequest $request): JsonResponse
    {
        try {
            $assignment = $this->assignmentService->assignEvaluator($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Evaluador asignado exitosamente',
                'data' => $assignment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar evaluador',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Auto-assign evaluators (distribute automatically).
     * POST /api/evaluator-assignments/auto-assign
     */
    public function autoAssign(AutoAssignEvaluatorsRequest $request): JsonResponse
    {
        try {
            $assignments = $this->assignmentService->autoAssignEvaluators(
                $request->input('job_posting_id'),
                $request->input('phase_id'),
                $request->input('evaluator_ids'),
                $request->input('application_ids')
            );

            // Notificar a evaluadores
            $this->assignmentService->notifyAssignments($assignments);

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    'Se asignaron %d evaluaciones exitosamente',
                    $assignments->count()
                ),
                'data' => $assignments,
                'summary' => [
                    'total_assignments' => $assignments->count(),
                    'evaluators_count' => count($request->input('evaluator_ids')),
                    'applications_count' => count($request->input('application_ids')),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la asignación automática',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reassign an evaluation to another evaluator.
     * POST /api/evaluator-assignments/{id}/reassign
     */
    public function reassign(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'new_evaluator_id' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        try {
            $newAssignment = $this->assignmentService->reassignEvaluation(
                $id,
                $request->input('new_evaluator_id'),
                $request->input('reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'Evaluación reasignada exitosamente',
                'data' => $newAssignment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reasignar la evaluación',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel an assignment.
     * POST /api/evaluator-assignments/{id}/cancel
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $assignment = EvaluatorAssignment::findOrFail($id);
            $assignment->cancel($request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Asignación cancelada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la asignación',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get evaluator workload.
     * GET /api/evaluator-assignments/workload
     */
    public function workload(Request $request): JsonResponse
    {
        $request->validate([
            'evaluator_ids' => ['required', 'array'],
            'evaluator_ids.*' => ['integer', 'exists:users,id'],
            'phase_id' => ['nullable', 'integer', 'exists:process_phases,id'],
        ]);

        try {
            $workload = $this->assignmentService->getEvaluatorWorkload(
                $request->input('evaluator_ids'),
                $request->input('phase_id')
            );

            return response()->json([
                'success' => true,
                'data' => $workload,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la carga de trabajo',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get assignments for a specific evaluator.
     * GET /api/evaluator-assignments/by-evaluator/{evaluatorId}
     */
    public function byEvaluator(Request $request, int $evaluatorId): JsonResponse
    {
        try {
            $filters = [
                'status' => $request->input('status'),
                'phase_id' => $request->input('phase_id'),
                'pending_only' => $request->boolean('pending_only'),
                'per_page' => $request->input('per_page', 15),
            ];

            $assignments = $this->assignmentService->getEvaluatorAssignments(
                $evaluatorId,
                $filters
            );

            return response()->json([
                'success' => true,
                'data' => $assignments->items(),
                'meta' => [
                    'current_page' => $assignments->currentPage(),
                    'total' => $assignments->total(),
                    'per_page' => $assignments->perPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las asignaciones',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get my assignments (authenticated evaluator).
     * GET /api/evaluator-assignments/my-assignments
     */
    public function myAssignments(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->input('status'),
            'phase_id' => $request->input('phase_id'),
            'pending_only' => $request->boolean('pending_only'),
            'per_page' => $request->input('per_page', 15),
        ];

        $assignments = $this->assignmentService->getEvaluatorAssignments(
            auth()->id(),
            $filters
        );

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
     * Get assignment statistics.
     * GET /api/evaluator-assignments/stats
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $filters = [
                'job_posting_id' => $request->input('job_posting_id'),
                'phase_id' => $request->input('phase_id'),
            ];

            $stats = $this->assignmentService->getAssignmentStats($filters);

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
     * Display the specified assignment.
     * GET /api/evaluator-assignments/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $assignment = EvaluatorAssignment::with([
                'evaluator',
                'application',
                'phase',
                'jobPosting',
                'assignedBy'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $assignment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Asignación no encontrada',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}