<?php

namespace Modules\Evaluation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Evaluation\Entities\EvaluationCriterion;

class EvaluationCriterionController extends Controller
{
    /**
     * Display a listing of evaluation criteria.
     * GET /api/evaluation-criteria
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EvaluationCriterion::with('phase');

            // Filtros
            if ($request->has('phase_id')) {
                $query->byPhase($request->input('phase_id'));
            }

            if ($request->has('job_posting_id')) {
                $query->byJobPosting($request->input('job_posting_id'));
            }

            if ($request->boolean('active_only')) {
                $query->active();
            }

            if ($request->boolean('system_only')) {
                $query->system();
            }

            // Ordenamiento
            $query->ordered();

            $criteria = $query->get();

            return response()->json([
                'success' => true,
                'data' => $criteria,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los criterios',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store a newly created criterion.
     * POST /api/evaluation-criteria
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phase_id' => ['required', 'integer', 'exists:process_phases,id'],
            'job_posting_id' => ['nullable', 'integer', 'exists:job_postings,id'],
            'code' => ['required', 'string', 'max:50', 'unique:evaluation_criteria,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'min_score' => ['required', 'numeric', 'min:0'],
            'max_score' => ['required', 'numeric', 'gt:min_score'],
            'weight' => ['required', 'numeric', 'min:0', 'max:10'],
            'order' => ['nullable', 'integer', 'min:0'],
            'requires_comment' => ['nullable', 'boolean'],
            'requires_evidence' => ['nullable', 'boolean'],
            'score_type' => ['required', 'string', 'in:NUMERIC,PERCENTAGE,QUALITATIVE'],
            'score_scales' => ['nullable', 'array'],
            'evaluation_guide' => ['nullable', 'string'],
        ]);

        try {
            $criterion = EvaluationCriterion::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Criterio de evaluación creado exitosamente',
                'data' => $criterion,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el criterio',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified criterion.
     * GET /api/evaluation-criteria/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $criterion = EvaluationCriterion::with('phase', 'jobPosting')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $criterion,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Criterio no encontrado',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified criterion.
     * PUT /api/evaluation-criteria/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'min_score' => ['sometimes', 'required', 'numeric', 'min:0'],
            'max_score' => ['sometimes', 'required', 'numeric'],
            'weight' => ['sometimes', 'required', 'numeric', 'min:0', 'max:10'],
            'order' => ['nullable', 'integer', 'min:0'],
            'requires_comment' => ['nullable', 'boolean'],
            'requires_evidence' => ['nullable', 'boolean'],
            'score_type' => ['sometimes', 'required', 'string', 'in:NUMERIC,PERCENTAGE,QUALITATIVE'],
            'score_scales' => ['nullable', 'array'],
            'evaluation_guide' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            $criterion = EvaluationCriterion::findOrFail($id);

            // Verificar si es un criterio del sistema
            if ($criterion->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden modificar criterios del sistema',
                ], 403);
            }

            $criterion->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Criterio actualizado exitosamente',
                'data' => $criterion->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el criterio',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified criterion.
     * DELETE /api/evaluation-criteria/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $criterion = EvaluationCriterion::findOrFail($id);

            // Verificar si es un criterio del sistema
            if ($criterion->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar criterios del sistema',
                ], 403);
            }

            // Verificar si tiene evaluaciones asociadas
            if ($criterion->details()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un criterio que tiene evaluaciones asociadas',
                ], 422);
            }

            $criterion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Criterio eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el criterio',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get criteria by phase.
     * GET /api/evaluation-criteria/by-phase/{phaseId}
     */
    public function byPhase(int $phaseId): JsonResponse
    {
        try {
            $criteria = EvaluationCriterion::active()
                ->byPhase($phaseId)
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $criteria,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los criterios',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get criteria for a specific job posting and phase.
     * GET /api/evaluation-criteria/for-evaluation
     */
    public function forEvaluation(Request $request): JsonResponse
    {
        $request->validate([
            'phase_id' => ['required', 'integer', 'exists:process_phases,id'],
            'job_posting_id' => ['required', 'integer', 'exists:job_postings,id'],
        ]);

        try {
            $criteria = EvaluationCriterion::active()
                ->byPhase($request->input('phase_id'))
                ->byJobPosting($request->input('job_posting_id'))
                ->ordered()
                ->get();

            // Calcular el puntaje máximo total
            $maxTotalScore = $criteria->sum(function ($criterion) {
                return $criterion->max_score * $criterion->weight;
            });

            return response()->json([
                'success' => true,
                'data' => $criteria,
                'meta' => [
                    'total_criteria' => $criteria->count(),
                    'max_total_score' => round($maxTotalScore, 2),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los criterios',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Toggle criterion active status.
     * POST /api/evaluation-criteria/{id}/toggle-active
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $criterion = EvaluationCriterion::findOrFail($id);

            if ($criterion->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede desactivar un criterio del sistema',
                ], 403);
            }

            $criterion->update(['is_active' => !$criterion->is_active]);

            return response()->json([
                'success' => true,
                'message' => $criterion->is_active ? 'Criterio activado' : 'Criterio desactivado',
                'data' => $criterion,
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
     * Reorder criteria.
     * POST /api/evaluation-criteria/reorder
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'criteria' => ['required', 'array'],
            'criteria.*.id' => ['required', 'integer', 'exists:evaluation_criteria,id'],
            'criteria.*.order' => ['required', 'integer', 'min:0'],
        ]);

        try {
            foreach ($request->input('criteria') as $item) {
                EvaluationCriterion::where('id', $item['id'])
                    ->update(['order' => $item['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reordenar criterios',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}