<?php

namespace Modules\Jury\Http\Controllers;

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Routing\Controller;
use Modules\Jury\Services\JuryAssignmentService;
use Modules\Jury\Http\Requests\AssignJuryRequest;

class JuryAssignmentController extends Controller
{
    public function __construct(
        protected JuryAssignmentService $service
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['job_posting_id', 'jury_member_id', 'member_type', 'role_in_jury', 'status', 'per_page']);
        $assignments = $this->service->getAll($filters);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['success' => true, 'data' => $assignments]);
        }

        return view('jury::assignments.index', compact('assignments', 'filters'));
    }

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

    public function show(Request $request, string $id)
    {
        try {
            $assignment = \Modules\Jury\Entities\JuryAssignment::with([
                'juryMember.user',
                'jobPosting',
                'assignedBy',
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

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->service->remove($id);
            return response()->json(['success' => true, 'message' => 'Asignación eliminada']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function replace(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'new_jury_member_id' => ['required', 'string', 'exists:jury_members,id'],
            'reason' => ['required', 'string'],
        ]);

        try {
            $assignment = $this->service->replace($id, $validated['new_jury_member_id'], $validated['reason']);
            return response()->json(['success' => true, 'message' => 'Jurado reemplazado', 'data' => $assignment]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function excuse(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string']]);

        try {
            $assignment = $this->service->excuse($id, $validated['reason']);
            return response()->json(['success' => true, 'message' => 'Jurado excusado', 'data' => $assignment]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function autoAssign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'string', 'exists:job_postings,id'],
            'total_titulares' => ['required', 'integer', 'min:1', 'max:10'],
            'total_suplentes' => ['required', 'integer', 'min:0', 'max:10'],
            'preferred_specialties' => ['nullable', 'array'],
        ]);

        try {
            $assignments = $this->service->autoAssign(
                $validated['job_posting_id'],
                $validated['total_titulares'],
                $validated['total_suplentes'],
                $validated['preferred_specialties'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Jurados asignados automáticamente',
                'data' => $assignments,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function workloadStatistics(string $jobPostingId): JsonResponse
    {
        try {
            $stats = $this->service->getWorkloadStatistics($jobPostingId);
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function balanceWorkload(string $jobPostingId): JsonResponse
    {
        try {
            $result = $this->service->balanceWorkload($jobPostingId);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function byJobPosting(string $jobPostingId): JsonResponse
    {
        try {
            $assignments = $this->service->getByJobPosting($jobPostingId);
            return response()->json(['success' => true, 'data' => $assignments]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function availableEvaluators(Request $request, string $jobPostingId): JsonResponse
    {
        $phaseId = $request->input('phase_id');
        $applicationId = $request->input('application_id');

        try {
            $evaluators = $this->service->getAvailableEvaluators($jobPostingId, $phaseId, $applicationId);
            return response()->json(['success' => true, 'data' => $evaluators]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}