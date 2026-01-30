<?php

namespace Modules\Evaluation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Evaluation\Services\EvaluationService;
use Modules\Evaluation\Entities\{Evaluation, EvaluationCriterion};
use Modules\Evaluation\Http\Requests\{BulkEditScoreRequest, LoadBulkEditDataRequest};
use Modules\Evaluation\Http\Resources\BulkEditEvaluationResource;
use Modules\JobPosting\Entities\{JobPosting, ProcessPhase};

class BulkEditEvaluationController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->middleware('auth');
        $this->middleware('can:assign-evaluators');
        $this->evaluationService = $evaluationService;
    }

    /**
     * Vista inicial: Selección de JobPosting y Phase
     * GET /evaluation/bulk-edit
     */
    public function index()
    {
        $jobPostings = JobPosting::with(['schedules.phase'])
            ->whereIn('status', ['PUBLICADA', 'EN_PROCESO', 'FINALIZADA'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('evaluation::bulk-edit.index', compact('jobPostings'));
    }

    /**
     * Vista de edición masiva: Tabla con todas las evaluaciones
     * GET /evaluation/bulk-edit/edit?job_posting_id={uuid}&phase_id={uuid}
     */
    public function edit(Request $request)
    {
        // Validar parámetros
        $validated = $request->validate([
            'job_posting_id' => ['required', 'exists:job_postings,id'],
            'phase_id' => ['required', 'exists:process_phases,id'],
        ]);

        $jobPosting = JobPosting::findOrFail($validated['job_posting_id']);
        $phase = ProcessPhase::findOrFail($validated['phase_id']);

        // Obtener criterios de evaluación para esta fase/convocatoria
        $criteria = EvaluationCriterion::active()
            ->byPhase($phase->id)
            ->byJobPosting($jobPosting->id)
            ->ordered()
            ->get();

        if ($criteria->isEmpty()) {
            return redirect()->route('evaluation.bulk-edit.index')
                ->with('error', 'No hay criterios de evaluación definidos para esta fase y convocatoria.');
        }

        // Obtener todas las evaluaciones con sus relaciones
        $evaluations = Evaluation::with([
                'application.jobProfile.positionCode',
                'application.jobProfile.requestingUnit',
                'evaluator',
                'details.criterion',
            ])
            ->where('job_posting_id', $jobPosting->id)
            ->where('phase_id', $phase->id)
            ->whereIn('status', ['SUBMITTED', 'MODIFIED'])
            ->get();

        // Transformar evaluaciones para la vista
        $transformedEvaluations = $evaluations->map(function ($evaluation) {
            return [
                'id' => $evaluation->id,
                'status' => $evaluation->status->value,
                'status_label' => $evaluation->status->label(),
                'total_score' => (float) ($evaluation->total_score ?? 0),
                'percentage' => (float) ($evaluation->percentage ?? 0),
                'application' => [
                    'full_name' => $evaluation->application->full_name ?? 'N/A',
                    'dni' => $evaluation->application->dni ?? 'N/A',
                    'position_code' => $evaluation->application->jobProfile->positionCode->code ?? 'N/A',
                ],
                'details' => $evaluation->details->mapWithKeys(function ($detail) {
                    return [
                        'criterion_' . $detail->criterion_id => [
                            'detail_id' => $detail->id,
                            'score' => (float) ($detail->score ?? 0),
                            'weighted_score' => (float) ($detail->weighted_score ?? 0),
                            'version' => $detail->version,
                        ]
                    ];
                }),
                'can_edit' => in_array($evaluation->status->value, ['SUBMITTED', 'MODIFIED']),
            ];
        });

        // Filtros aplicados
        $filters = [
            'search' => $request->get('search', ''),
            'score_min' => $request->get('score_min', ''),
            'score_max' => $request->get('score_max', ''),
            'status' => $request->get('status', []),
        ];

        return view('evaluation::bulk-edit.edit', compact(
            'jobPosting',
            'phase',
            'criteria',
            'transformedEvaluations',
            'filters'
        ));
    }

    /**
     * API Endpoint: Cargar datos de evaluaciones (con filtros)
     * GET /evaluation/bulk-edit/data?job_posting_id={uuid}&phase_id={uuid}&filters...
     */
    public function loadData(Request $request)
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'exists:job_postings,id'],
            'phase_id' => ['required', 'exists:process_phases,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'score_min' => ['nullable', 'numeric', 'min:0'],
            'score_max' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'array'],
            'status.*' => ['string', 'in:SUBMITTED,MODIFIED,IN_PROGRESS'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $jobPosting = JobPosting::findOrFail($validated['job_posting_id']);
        $phase = ProcessPhase::findOrFail($validated['phase_id']);

        // Query base
        $query = Evaluation::with([
                'application.jobProfile.positionCode',
                'evaluator',
                'details.criterion',
            ])
            ->where('job_posting_id', $jobPosting->id)
            ->where('phase_id', $phase->id)
            ->whereIn('status', ['SUBMITTED', 'MODIFIED']);

        // Aplicar filtros
        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->whereHas('application', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['score_min'])) {
            $query->where('total_score', '>=', $validated['score_min']);
        }

        if (!empty($validated['score_max'])) {
            $query->where('total_score', '<=', $validated['score_max']);
        }

        if (!empty($validated['status'])) {
            $query->whereIn('status', $validated['status']);
        }

        // Ordenar por nombre de postulante
        $query->join('applications', 'evaluations.application_id', '=', 'applications.id')
            ->select('evaluations.*')
            ->orderBy('applications.full_name', 'asc');

        // Paginar
        $evaluations = $query->paginate(50);

        return BulkEditEvaluationResource::collection($evaluations);
    }

    /**
     * API Endpoint: Actualizar un puntaje específico
     * POST /evaluation/bulk-edit/update-score
     * Body: {evaluation_id, criterion_id, score}
     */
    public function updateScore(BulkEditScoreRequest $request)
    {
        $result = $this->evaluationService->bulkUpdateScore(
            $request->evaluation_id,
            $request->criterion_id,
            $request->score
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data'],
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'errors' => ['score' => [$result['message']]],
            ], 422);
        }
    }

    /**
     * API Endpoint: Obtener criterios de evaluación para una fase/convocatoria
     * GET /evaluation/bulk-edit/criteria?job_posting_id={uuid}&phase_id={uuid}
     */
    public function getCriteria(Request $request)
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'exists:job_postings,id'],
            'phase_id' => ['required', 'exists:process_phases,id'],
        ]);

        $jobPosting = JobPosting::findOrFail($validated['job_posting_id']);
        $phase = ProcessPhase::findOrFail($validated['phase_id']);

        $criteria = EvaluationCriterion::active()
            ->byPhase($phase->id)
            ->byJobPosting($jobPosting->id)
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $criteria->map(function ($criterion) {
                return [
                    'id' => $criterion->id,
                    'code' => $criterion->code,
                    'name' => $criterion->name,
                    'min_score' => $criterion->min_score,
                    'max_score' => $criterion->max_score,
                    'weight' => $criterion->weight,
                ];
            }),
        ]);
    }

    /**
     * API Endpoint: Obtener fases de una convocatoria
     * GET /evaluation/bulk-edit/phases?job_posting_id={uuid}
     */
    public function getPhases(Request $request)
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'exists:job_postings,id'],
        ]);

        $jobPosting = JobPosting::with('schedules.phase')
            ->findOrFail($validated['job_posting_id']);

        // Obtener las fases únicas de los cronogramas
        $phases = $jobPosting->schedules
            ->pluck('phase')
            ->filter()
            ->unique('id')
            ->sortBy('phase_number')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $phases->map(function ($phase) {
                return [
                    'id' => $phase->id,
                    'name' => $phase->name,
                    'order' => $phase->order ?? $phase->phase_number,
                ];
            }),
        ]);
    }
}
