<?php

namespace Modules\Jury\Http\Controllers;

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Routing\Controller;
use Modules\Jury\Services\JuryMemberService;
use Modules\Jury\Http\Requests\{StoreJuryMemberRequest, UpdateJuryMemberRequest};

class JuryMemberController extends Controller
{
    public function __construct(
        protected JuryMemberService $service
    ) {}

    /**
     * Display a listing
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $members = $this->service->getAll($filters);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $members,
            ]);
        }

        return view('jury::members.index', compact('members', 'filters'));
    }

    /**
     * Show the form for creating
     */
    public function create()
    {
        return view('jury::members.create');
    }

    /**
     * Store a newly created resource
     */
    public function store(StoreJuryMemberRequest $request): JsonResponse
    {
        try {
            $member = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Jurado creado exitosamente',
                'data' => $member,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el jurado',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get statistics
     */
    public function statistics(string $id): JsonResponse
    {
        try {
            $statistics = $this->service->getStatistics($id);

            return response()->json([
                'success' => true,
                'data' => $statistics,
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
     * Get workload summary
     */
    public function workloadSummary(): JsonResponse
    {
        try {
            $summary = $this->service->getWorkloadSummary();

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen de carga',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get available members for assignment
     */
    public function availableForAssignment(Request $request): JsonResponse
    {
        $filters = $request->only(['specialty', 'exclude_ids']);

        try {
            $members = $this->service->getAvailableForAssignment($filters);

            return response()->json([
                'success' => true,
                'data' => $members,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener jurados disponibles',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
