<?php

namespace Modules\Jury\Http\Controllers;

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Routing\Controller;
use Modules\Jury\Services\JuryAssignmentService;
use Modules\Jury\Services\JuryMemberService;
use Modules\Jury\Http\Requests\AssignJuryRequest;

class JuryAssignmentController extends Controller
{
    public function __construct(
        protected JuryAssignmentService $service,
        protected JuryMemberService $memberService
    ) {}

    /**
     * Listar asignaciones de jurados con filtros
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'job_posting_id',
            'user_id',
            'role_in_jury',
            'dependency_scope_id',
            'status',
            'per_page',
            'include_inactive'
        ]);

        $assignments = $this->service->getAll($filters);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['success' => true, 'data' => $assignments]);
        }

        // Datos para la vista
        $jobPostings = \Modules\JobPosting\Entities\JobPosting::active()
            ->orderBy('created_at', 'desc')
            ->get();

        // Usuarios con rol JURADO
        $jurors = $this->memberService->getAll([]);

        // Dependencias para el filtro de scope
        $dependencies = \Modules\Organization\Entities\OrganizationalUnit::orderBy('name')->get();

        // Estadísticas
        $activeCount = $assignments->where('status', \Modules\Jury\Enums\AssignmentStatus::ACTIVE)->count();
        $inactiveCount = $assignments->where('status', \Modules\Jury\Enums\AssignmentStatus::INACTIVE)->count();

        return view('jury::assignments.index', compact(
            'assignments',
            'filters',
            'jobPostings',
            'jurors',
            'dependencies',
            'activeCount',
            'inactiveCount'
        ));
    }

    /**
     * Asignar jurado a convocatoria
     */
    public function store(AssignJuryRequest $request): JsonResponse
    {
        try {
            $assignment = $this->service->assign($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Jurado asignado exitosamente',
                'data' => $assignment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Ver detalle de una asignación
     */
    public function show(Request $request, string $id)
    {
        try {
            $assignment = \Modules\Jury\Entities\JuryAssignment::with([
                'user',
                'jobPosting',
                'assignedBy',
                'dependencyScope',
            ])->findOrFail($id);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['success' => true, 'data' => $assignment]);
            }

            return view('jury::assignments.show', compact('assignment'));
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'No encontrado'], 404);
            }
            abort(404);
        }
    }

    /**
     * Desactivar asignación de jurado
     */
    public function deactivate(string $id): JsonResponse
    {
        try {
            $assignment = $this->service->deactivate($id);
            return response()->json([
                'success' => true,
                'message' => 'Asignación desactivada exitosamente',
                'data' => $assignment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Activar asignación de jurado
     */
    public function activate(string $id): JsonResponse
    {
        try {
            $assignment = $this->service->activate($id);
            return response()->json([
                'success' => true,
                'message' => 'Asignación activada exitosamente',
                'data' => $assignment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Asignación automática de jurados a convocatoria
     * Distribuye equitativamente según carga de trabajo
     */
    public function autoAssign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'string', 'exists:job_postings,id'],
            'total_jurors' => ['required', 'integer', 'min:1', 'max:10'],
            'preferred_roles' => ['nullable', 'array'],
            'preferred_roles.*' => ['string', 'in:PRESIDENTE,SECRETARIO,VOCAL,MIEMBRO'],
        ]);

        try {
            $assignments = $this->service->autoAssign(
                $validated['job_posting_id'],
                $validated['total_jurors'],
                $validated['preferred_roles'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Jurados asignados automáticamente',
                'data' => $assignments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener estadísticas de carga de trabajo para una convocatoria
     */
    public function workloadStatistics(string $jobPostingId): JsonResponse
    {
        try {
            $stats = $this->service->getWorkloadStatistics($jobPostingId);
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener asignaciones por convocatoria
     */
    public function byJobPosting(string $jobPostingId): JsonResponse
    {
        try {
            $assignments = $this->service->getByJobPosting($jobPostingId);
            return response()->json(['success' => true, 'data' => $assignments]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener evaluadores disponibles para una convocatoria
     * Opcionalmente filtrar por postulación (excluye conflictos)
     */
    public function availableEvaluators(Request $request, string $jobPostingId): JsonResponse
    {
        $applicationId = $request->input('application_id');

        try {
            $evaluators = $this->service->getAvailableEvaluators($jobPostingId, $applicationId);
            return response()->json(['success' => true, 'data' => $evaluators]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Sugerir mejor jurado disponible para una asignación
     * Basado en carga de trabajo y disponibilidad
     */
    public function suggestBestJuror(Request $request, string $jobPostingId): JsonResponse
    {
        $applicationId = $request->input('application_id');

        try {
            $suggestion = $this->service->suggestBestJuror($jobPostingId, $applicationId);

            if (!$suggestion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay jurados disponibles',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $suggestion,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
