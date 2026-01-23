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
     * Asignación automática masiva
     * Distribuye todas las postulaciones de una convocatoria entre los jurados
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

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result,
                ]);
            }

            return redirect()->route('evaluator-assignments.index', [
                'job_posting_id' => $validated['job_posting_id']
            ])->with('success', $result['message']);

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
