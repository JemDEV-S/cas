<?php

namespace Modules\Evaluation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Evaluation\Entities\EvaluationCriterion;
use Modules\JobPosting\Entities\ProcessPhase;

class EvaluationCriterionController extends Controller
{
    /**
     * Display a listing of evaluation criteria.
     * GET /evaluation-criteria
     */
    public function index(Request $request)
    {
        // Si es petición AJAX/API, devolver JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return $this->indexApi($request);
        }

        // Si es web, devolver vista
        $query = EvaluationCriterion::with('phase');

        // Aplicar filtros
        if ($request->has('phase_id')) {
            $query->byPhase($request->input('phase_id'));
        }

        if ($request->has('job_posting_id')) {
            $query->byJobPosting($request->input('job_posting_id'));
        }

        if ($request->has('active_only')) {
            if ($request->boolean('active_only')) {
                $query->active();
            }
        }

        if ($request->has('system_only')) {
            if ($request->boolean('system_only')) {
                $query->system();
            }
        }

        $query->ordered();
        $criteria = $query->get();

        // Agrupar por fase
        $criteriaByPhase = $criteria->groupBy(function ($criterion) {
            return $criterion->phase->name ?? 'Sin Fase';
        });

        // Obtener fases para filtros
        $phases = ProcessPhase::orderBy('order')->get();

        return view('evaluation::criteria.index', [
            'criteriaByPhase' => $criteriaByPhase,
            'phases' => $phases,
        ]);
    }

    /**
     * API version of index
     */
    protected function indexApi(Request $request): JsonResponse
    {
        try {
            $query = EvaluationCriterion::with('phase');

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
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phase_id' => ['required', 'string', 'exists:process_phases,id'],
            'job_posting_id' => ['nullable', 'string', 'exists:job_postings,id'],
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
     */
    public function show(int $id)
    {
        try {
            $criterion = EvaluationCriterion::with('phase', 'jobPosting')->findOrFail($id);

            // Si es API, devolver JSON
            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $criterion,
                ]);
            }

            // Si es web, devolver vista
            return view('evaluation::criteria.show', compact('criterion'));
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Criterio no encontrado',
                    'error' => $e->getMessage(),
                ], 404);
            }

            abort(404);
        }
    }

    /**
     * Show the form for creating a new criterion.
     * GET /evaluation-criteria/create
     */
    public function create()
    {
        $phases = \Modules\JobPosting\Entities\ProcessPhase::where('is_active', true)
            ->orderBy('order')
            ->get();

        $jobPostings = \Modules\JobPosting\Entities\JobPosting::where('status', 'PUBLISHED')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('evaluation::criteria.create', compact('phases', 'jobPostings'));
    }

    /**
     * Show the form for editing the specified criterion.
     * GET /evaluation-criteria/{id}/edit
     */
    public function edit(string $id)
    {
        $criterion = \Modules\Evaluation\Entities\EvaluationCriterion::findOrFail($id);

        $phases = \Modules\JobPosting\Entities\ProcessPhase::where('is_active', true)
            ->orderBy('order')
            ->get();

        $jobPostings = \Modules\JobPosting\Entities\JobPosting::where('status', 'PUBLISHED')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('evaluation::criteria.edit', compact('criterion', 'phases', 'jobPostings'));
    }

    /**
     * Update the specified criterion.
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
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $criterion = EvaluationCriterion::findOrFail($id);

            if ($criterion->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar criterios del sistema',
                ], 403);
            }

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
     */
    public function byPhase(string $phaseId): JsonResponse
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
     */
    public function forEvaluation(Request $request): JsonResponse
    {
        $request->validate([
            'phase_id' => ['required', 'string', 'exists:process_phases,id'],
            'job_posting_id' => ['required', 'string', 'exists:job_postings,id'],
        ]);

        try {
            $criteria = EvaluationCriterion::active()
                ->byPhase($request->input('phase_id'))
                ->byJobPosting($request->input('job_posting_id'))
                ->ordered()
                ->get();

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
