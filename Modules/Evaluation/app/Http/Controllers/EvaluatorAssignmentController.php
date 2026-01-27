<?php

namespace Modules\Evaluation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Evaluation\Services\EvaluatorAssignmentService;
use Modules\Evaluation\Entities\EvaluatorAssignment;

class EvaluatorAssignmentController extends Controller
{
    protected EvaluatorAssignmentService $assignmentService;

    public function __construct(EvaluatorAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display a listing of assignments.
     * WEB: GET /evaluator-assignments
     * API: GET /api/evaluator-assignments
     */
    public function index(Request $request)
    {
        try {
            // Query base con relaciones optimizadas
            $query = EvaluatorAssignment::with([
                'user',
                'application.jobProfile.jobPosting',
                'phase',
                'assignedBy'
            ]);

            // Filtros
            if ($request->has('user_id')) {
                $query->byUser($request->input('user_id'));
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

            // Búsqueda por evaluador
            if ($request->has('evaluator')) {
                $search = $request->input('evaluator');
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $query->orderBy('created_at', 'desc');

            $assignments = $query->paginate($request->input('per_page', 15));

            // Estadísticas básicas
            $stats = [
                'total' => EvaluatorAssignment::count(),
                'pending' => EvaluatorAssignment::where('status', 'PENDING')->count(),
                'completed' => EvaluatorAssignment::where('status', 'COMPLETED')->count(),
                'overdue' => EvaluatorAssignment::whereNotNull('deadline_at')
                    ->where('deadline_at', '<', now())
                    ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
                    ->count(),
            ];

            // Carga de trabajo por evaluador
            $workloadStats = collect([]);

            try {
                $workloadData = \DB::table('evaluator_assignments')
                    ->select(
                        'user_id',
                        \DB::raw('COUNT(*) as total'),
                        \DB::raw('SUM(CASE WHEN status = "PENDING" THEN 1 ELSE 0 END) as pending'),
                        \DB::raw('SUM(CASE WHEN status = "COMPLETED" THEN 1 ELSE 0 END) as completed')
                    )
                    ->whereNull('deleted_at')
                    ->groupBy('user_id')
                    ->get();

                // Enriquecer con datos de usuario y jury assignment
                $workloadStats = $workloadData->map(function($item) {
                    $user = \Modules\User\Entities\User::find($item->user_id);

                    if (!$user) {
                        return null;
                    }

                    // Obtener una asignación activa del jurado (puede tener varias)
                    $juryAssignment = \Modules\Jury\Entities\JuryAssignment::where('user_id', $item->user_id)
                        ->where('status', 'ACTIVE')
                        ->first();

                    return (object)[
                        'user_id' => $item->user_id,
                        'evaluator_name' => $user->getFullNameAttribute() ?? 'N/A',
                        'email' => $user->email ?? 'N/A',
                        'role' => $juryAssignment?->role_in_jury?->label() ?? 'N/A',
                        'total' => $item->total,
                        'pending' => $item->pending,
                        'completed' => $item->completed,
                        'completion_rate' => $item->total > 0
                            ? round(($item->completed / $item->total) * 100, 0)
                            : 0,
                    ];
                })->filter()->values();

            } catch (\Exception $e) {
                \Log::error('Error calculating workload stats: ' . $e->getMessage());
            }

            // Job Postings para filtros
            $jobPostings = \Modules\JobPosting\Entities\JobPosting::where('status', 'PUBLICADA')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get(['id', 'title', 'code']);

            // Fases para filtros
            $phases = \Modules\JobPosting\Entities\ProcessPhase::where('is_active', true)
                ->orderBy('order')
                ->get(['id', 'name']);

            // Si es petición AJAX/API
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $assignments->items(),
                    'stats' => $stats,
                    'workload' => $workloadStats,
                    'meta' => [
                        'current_page' => $assignments->currentPage(),
                        'total' => $assignments->total(),
                        'per_page' => $assignments->perPage(),
                    ],
                ]);
            }

            // Retornar vista
            return view('evaluation::assignments.index', compact(
                'assignments',
                'stats',
                'workloadStats',
                'jobPostings',
                'phases'
            ));

        } catch (\Exception $e) {
            \Log::error('Error in EvaluatorAssignmentController@index: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al obtener las asignaciones',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return view('evaluation::assignments.index', [
                'assignments' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'stats' => ['total' => 0, 'pending' => 0, 'completed' => 0, 'overdue' => 0],
                'workloadStats' => collect([]),
                'jobPostings' => collect([]),
                'phases' => collect([]),
            ])->with('error', 'Error al cargar las asignaciones: ' . $e->getMessage());
        }
    }

    /**
     * Assign evaluator manually to an application.
     * POST /evaluator-assignments
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_id' => ['required', 'string', 'exists:applications,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'phase_id' => ['required', 'string', 'exists:process_phases,id'],
            'deadline_at' => ['nullable', 'date', 'after:today'],
        ]);

        try {
            $assignment = $this->assignmentService->assignEvaluator($validated);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Evaluador asignado exitosamente',
                    'data' => $assignment,
                ], 201);
            }

            return redirect()->route('evaluator-assignments.index')
                ->with('success', 'Evaluador asignado exitosamente');

        } catch (\Exception $e) {
            \Log::error('Error assigning evaluator: ' . $e->getMessage());

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al asignar evaluador',
                    'error' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Vista de postulaciones activas con estado de evaluación
     * GET /evaluator-assignments/applications
     */
    public function applications(Request $request)
    {
        try {
            // Obtener filtros
            $jobPostingId = $request->input('job_posting_id');
            $phaseId = $request->input('phase_id');
            $evaluationStatus = $request->input('evaluation_status');
            $assignmentStatus = $request->input('assignment_status');
            $evaluatorId = $request->input('evaluator_id');
            $search = $request->input('search');

            // Query base
            $query = \Modules\Application\Entities\Application::query()
                ->with([
                    'applicant',
                    'jobProfile.jobPosting',
                    'evaluatorAssignments' => function($q) use ($phaseId) {
                        if ($phaseId) {
                            $q->where('phase_id', $phaseId);
                        }
                        $q->with(['user', 'phase']);
                    },
                    'evaluations' => function($q) use ($phaseId) {
                        if ($phaseId) {
                            $q->where('phase_id', $phaseId);
                        }
                        $q->with(['evaluator', 'phase']);
                    }
                ])
                ->where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE);

            // Filtro de búsqueda por texto
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('dni', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            // Filtro por convocatoria
            if ($jobPostingId) {
                $query->whereHas('jobProfile', function($q) use ($jobPostingId) {
                    $q->where('job_posting_id', $jobPostingId);
                });
            }

            // Filtro por estado de evaluación
            if ($evaluationStatus) {
                if ($evaluationStatus === 'WITHOUT_EVALUATION') {
                    $query->whereDoesntHave('evaluations', function($q) use ($phaseId) {
                        if ($phaseId) {
                            $q->where('phase_id', $phaseId);
                        }
                    });
                } else {
                    $query->whereHas('evaluations', function($q) use ($phaseId, $evaluationStatus) {
                        if ($phaseId) {
                            $q->where('phase_id', $phaseId);
                        }
                        $q->where('status', $evaluationStatus);
                    });
                }
            }

            // Filtro por estado de asignación
            if ($assignmentStatus) {
                if ($assignmentStatus === 'WITHOUT_ASSIGNMENT') {
                    $query->whereDoesntHave('evaluatorAssignments', function($q) use ($phaseId) {
                        if ($phaseId) {
                            $q->where('phase_id', $phaseId);
                        }
                    });
                } else {
                    $query->whereHas('evaluatorAssignments', function($q) use ($phaseId, $assignmentStatus) {
                        if ($phaseId) {
                            $q->where('phase_id', $phaseId);
                        }
                        $q->where('status', $assignmentStatus);
                    });
                }
            }

            // Filtro por evaluador
            if ($evaluatorId) {
                $query->whereHas('evaluatorAssignments', function($q) use ($phaseId, $evaluatorId) {
                    if ($phaseId) {
                        $q->where('phase_id', $phaseId);
                    }
                    $q->where('user_id', $evaluatorId);
                });
            }

            // Ordenar
            $query->orderBy('created_at', 'desc');

            $applications = $query->paginate(30);

            // Obtener datos para filtros
            $jobPostings = \Modules\JobPosting\Entities\JobPosting::where('status', 'PUBLICADA')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get(['id', 'title', 'code']);

            $phases = \Modules\JobPosting\Entities\ProcessPhase::where('is_active', true)
                ->orderBy('order')
                ->get(['id', 'name']);

            // Obtener evaluadores (jurados) para filtro
            $evaluators = \Modules\User\Entities\User::whereHas('juryAssignments', function($q) {
                $q->where('status', 'ACTIVE');
            })->get(['id', 'first_name', 'last_name', 'email']);

            // Estadísticas
            $stats = $this->calculateApplicationStats($jobPostingId, $phaseId);

            return view('evaluation::assignments.applications', compact(
                'applications',
                'jobPostings',
                'phases',
                'evaluators',
                'stats'
            ));

        } catch (\Exception $e) {
            \Log::error('Error in applications view: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return view('evaluation::assignments.applications', [
                'applications' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30),
                'jobPostings' => collect([]),
                'phases' => collect([]),
                'evaluators' => collect([]),
                'stats' => [],
            ])->with('error', 'Error al cargar las postulaciones: ' . $e->getMessage());
        }
    }

    /**
     * Calcular estadísticas de postulaciones
     */
    private function calculateApplicationStats($jobPostingId, $phaseId): array
    {
        $query = \Modules\Application\Entities\Application::where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE);

        if ($jobPostingId) {
            $query->whereHas('jobProfile', function($q) use ($jobPostingId) {
                $q->where('job_posting_id', $jobPostingId);
            });
        }

        $total = $query->count();

        // Con asignación
        $withAssignment = (clone $query)->whereHas('evaluatorAssignments', function($q) use ($phaseId) {
            if ($phaseId) {
                $q->where('phase_id', $phaseId);
            }
        })->count();

        // Sin asignación
        $withoutAssignment = (clone $query)->whereDoesntHave('evaluatorAssignments', function($q) use ($phaseId) {
            if ($phaseId) {
                $q->where('phase_id', $phaseId);
            }
        })->count();

        // Con evaluación
        $withEvaluation = (clone $query)->whereHas('evaluations', function($q) use ($phaseId) {
            if ($phaseId) {
                $q->where('phase_id', $phaseId);
            }
        })->count();

        // Evaluaciones completadas
        $evaluationsCompleted = (clone $query)->whereHas('evaluations', function($q) use ($phaseId) {
            if ($phaseId) {
                $q->where('phase_id', $phaseId);
            }
            $q->whereIn('status', [
                \Modules\Evaluation\Enums\EvaluationStatusEnum::SUBMITTED->value,
                \Modules\Evaluation\Enums\EvaluationStatusEnum::MODIFIED->value,
            ]);
        })->count();

        // Evaluaciones en progreso
        $evaluationsInProgress = (clone $query)->whereHas('evaluations', function($q) use ($phaseId) {
            if ($phaseId) {
                $q->where('phase_id', $phaseId);
            }
            $q->whereIn('status', [
                \Modules\Evaluation\Enums\EvaluationStatusEnum::ASSIGNED->value,
                \Modules\Evaluation\Enums\EvaluationStatusEnum::IN_PROGRESS->value,
            ]);
        })->count();

        return [
            'total' => $total,
            'with_assignment' => $withAssignment,
            'without_assignment' => $withoutAssignment,
            'with_evaluation' => $withEvaluation,
            'evaluations_completed' => $evaluationsCompleted,
            'evaluations_in_progress' => $evaluationsInProgress,
        ];
    }

    /**
     * Obtener evaluadores disponibles
     * ACTUALIZADO: Ahora usa JuryAssignmentService
     */
    public function availableEvaluators(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => ['required', 'string', 'exists:applications,id'],
            'phase_id' => ['nullable', 'string', 'exists:process_phases,id'],
        ]);

        try {
            $evaluators = $this->assignmentService->getAvailableEvaluators(
                $validated['application_id'],
                $validated['phase_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => $evaluators,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting available evaluators: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener métricas de distribución antes de asignar
     * GET /evaluator-assignments/distribution-metrics
     */
    public function distributionMetrics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'string', 'exists:job_postings,id'],
            'phase_id' => ['required', 'string', 'exists:process_phases,id'],
        ]);

        try {
            $metrics = $this->assignmentService->getDistributionMetrics(
                $validated['job_posting_id'],
                $validated['phase_id']
            );

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'message' => 'Métricas obtenidas exitosamente',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting distribution metrics: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Asignación automática masiva
     * Distribuye todas las postulaciones de una convocatoria entre los jurados
     * MEJORADO: Ahora excluye postulaciones con evaluaciones en progreso o completadas
     */
    public function autoAssign(Request $request)
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'string', 'exists:job_postings,id'],
            'phase_id' => ['required', 'string', 'exists:process_phases,id'],
            'only_unassigned' => ['nullable', 'boolean'],
        ]);

        try {
            $result = $this->assignmentService->distributeByJobPosting(
                $validated['job_posting_id'],
                $validated['phase_id'],
                $validated['only_unassigned'] ?? true
            );

            // Construir mensaje detallado
            $message = $result['message'];
            if (isset($result['unassignable']) && $result['unassignable'] > 0) {
                $message .= " | {$result['unassignable']} postulaciones sin evaluador disponible (todos los jurados tienen conflictos)";
            }
            if (isset($result['conflicts']) && $result['conflicts'] > 0) {
                $message .= " | {$result['conflicts']} conflictos de interés resueltos automáticamente";
            }

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $result,
                ]);
            }

            $flashMessage = $message;
            $flashType = 'success';

            // Si hay postulaciones sin asignar por conflictos, cambiar a warning
            if (isset($result['unassignable']) && $result['unassignable'] > 0) {
                $flashType = 'warning';
            }

            return redirect()->route('evaluator-assignments.index', [
                'job_posting_id' => $validated['job_posting_id']
            ])->with($flashType, $flashMessage);

        } catch (\Exception $e) {
            \Log::error('Error in auto-assign: ' . $e->getMessage());

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel an assignment.
     * DELETE /evaluator-assignments/{id}
     */
    public function destroy(string $id)
    {
        try {
            $assignment = EvaluatorAssignment::findOrFail($id);

            // Cancelar la asignación (soft delete)
            $assignment->cancel('Cancelado por administrador');

            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Asignación cancelada exitosamente',
                ]);
            }

            return redirect()->route('evaluator-assignments.index')
                ->with('success', 'Asignación cancelada exitosamente');

        } catch (\Exception $e) {
            \Log::error('Error canceling assignment: ' . $e->getMessage());

            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cancelar la asignación',
                    'error' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified assignment.
     * GET /evaluator-assignments/{id}
     */
    public function show(string $id)
    {
        try {
            $assignment = EvaluatorAssignment::with([
                'user',
                'application.jobPosting',
                'application.applicant',
                'phase',
                'assignedBy'
            ])->findOrFail($id);

            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $assignment,
                ]);
            }

            return view('evaluation::assignments.show', compact('assignment'));

        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación no encontrada',
                    'error' => $e->getMessage(),
                ], 404);
            }

            abort(404);
        }
    }
}
