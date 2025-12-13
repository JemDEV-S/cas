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
        $filters = $request->only(['search', 'is_active', 'is_available', 'training_completed', 'specialty', 'per_page']);
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
     * Display the specified resource
     */
    public function show(Request $request, string $id)
    {
        try {
            $member = \Modules\Jury\Entities\JuryMember::with(['user', 'assignments.jobPosting'])
                ->withWorkload()
                ->findOrFail($id);

            $statistics = $this->service->getStatistics($id);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $member,
                    'statistics' => $statistics,
                ]);
            }

            return view('jury::members.show', compact('member', 'statistics'));
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jurado no encontrado',
                ], 404);
            }

            abort(404);
        }
    }

    /**
     * Show the form for editing
     */
    public function edit(string $id)
    {
        $member = \Modules\Jury\Entities\JuryMember::with('user')->findOrFail($id);
        return view('jury::members.edit', compact('member'));
    }

    /**
     * Update the specified resource
     */
    public function update(UpdateJuryMemberRequest $request, string $id): JsonResponse
    {
        try {
            $member = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Jurado actualizado exitosamente',
                'data' => $member,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el jurado',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Jurado eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el jurado',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(string $id): JsonResponse
    {
        try {
            $member = $this->service->toggleActive($id);

            return response()->json([
                'success' => true,
                'message' => $member->is_active ? 'Jurado activado' : 'Jurado desactivado',
                'data' => $member,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mark as unavailable
     */
    public function markUnavailable(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after:from'],
        ]);

        try {
            $member = $this->service->markAsUnavailable(
                $id,
                $validated['reason'],
                isset($validated['from']) ? new \DateTime($validated['from']) : null,
                isset($validated['until']) ? new \DateTime($validated['until']) : null
            );

            return response()->json([
                'success' => true,
                'message' => 'Jurado marcado como no disponible',
                'data' => $member,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como no disponible',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mark as available
     */
    public function markAvailable(string $id): JsonResponse
    {
        try {
            $member = $this->service->markAsAvailable($id);

            return response()->json([
                'success' => true,
                'message' => 'Jurado marcado como disponible',
                'data' => $member,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como disponible',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Complete training
     */
    public function completeTraining(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'certificate_path' => ['nullable', 'string'],
        ]);

        try {
            $member = $this->service->completeTraining($id, $validated['certificate_path'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Capacitación completada exitosamente',
                'data' => $member,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al completar la capacitación',
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
